<?php

class Utils {

    /**
     * 
     * @param string $url
     * @param boolean $bin
     * @param boolean $verbose
     * @param string $user_agent
     * @return string content
     */
    public static function curl_request($url,$bin=false,$verbose=false,$user_agent='Wget/1.11.4') {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_VERBOSE, $verbose);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($bin) curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
    }

    /**
     * 
     * @param string $url
     * @param string $dir
     * @param string $filename
     * @return boolean|string|int
     */
    public static function downloadToFile($url,$dir,$filename=false) {
        if (!$filename) $filename = basename($url);
        $file = $dir.$filename;
        // Retry 5 times before returning error
        for ($i=0;$i<5;$i++) {
            $content = Utils::curl_request($url,true);
            if ($content!==false) break;
        }
        // TODO: error handling
        if (strlen($content)<100) return false;
        $fh = fopen($file,'w');
        if (fwrite($fh,$content) === FALSE) {
            // TODO: error handling
            fclose($fh);
            return false;
        }
        fclose($fh);
        return $file;
    }

    /**
     * 
     * @param string $content
     * @return array links
     */
    public static function extractLinks($content) {
        $dom = new DOMDocument;
        $dom->loadHTML($content);
        $links = $dom->getElementsByTagName('a');
        $links_a=array();
        foreach ($links as $link) { $links_a[]=$link->getAttribute('href'); }
        return $links_a;
    }
    
    public function checkSSL($host,$all=false) {
        $g = stream_context_create (array("ssl" => array("capture_peer_cert" => true)));
        $r = stream_socket_client("ssl://$host:443",$errno, $errstr, 30, STREAM_CLIENT_CONNECT, $g);
        $cont = stream_context_get_params($r);
        $rdata = openssl_x509_read($cont["options"]["ssl"]["peer_certificate"]);
        $data = openssl_x509_parse($rdata);
        if ($all) { openssl_x509_free($rdata); return $data; }
        $sslinfo = array(
                'cn' => $data['subject']['CN'],
                'expires' => $data['validTo_time_t'],
        );
        openssl_x509_free($rdata);
        return $sslinfo;
    }

}



