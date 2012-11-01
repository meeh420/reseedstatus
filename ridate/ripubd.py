#!/usr/bin/python
import os,netdb,sys

def _print_hook(netdbe):
    print (netdbe['published'])

if len(sys.argv) != 2:
    print 'Usage: ripubd.py <dir>'
    sys.exit(-1)

netdb.inspect(_print_hook,sys.argv[1])
