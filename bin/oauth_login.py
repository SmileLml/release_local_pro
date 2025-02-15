#!/usr/bin/python3

import sys
import urllib.request
import urllib.parse
import json
import ssl

ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE


#SERVER = 'https://172.17.9.222/auth/oauth/fast_check'
SERVER = 'https://oauth.kylin.com/auth/oauth/fast_check'

if __name__ == '__main__':
    username, password = sys.argv[1:3]
    data = urllib.parse.urlencode({'username': username, 'password': password})
    output = urllib.request.urlopen(SERVER, data.encode('latin-1'), timeout=2, context=ctx).read()
    if output:
        print(json.loads(output.decode('utf-8'))['email'])
    else:
        sys.exit(1)
