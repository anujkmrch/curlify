import requests

class Curlify(object):
    #request url
	url = None;

	#User agent
	userAgent = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.62 Safari/537.36";

	# Reqeust parameters
	data = {};

	# is request a post request or get request
	isPost = False;

	isHead = False;
	isPut = False;
	isDelete = False;
	isTrace = False;
    isConnect = False;

	isSecure = False;
	isVerbose = False;

	debug = False;

    """docstring for Curlify."""
    def __init__(self, arg):
        super(Curlify, self).__init__()
        self.arg = arg

    def isDebug(self):
        self.debug != self.debug

    def getUrl(self):
        return self.url

    def setUrl(self,url):
        self.url = url

    def setData(self,key,value,subkey=None):
        if key in self.data:
            if type(self.data[key]) is not dict:
                temp = self.data[key]
                self.data[key] = {}
                self.data[key][] = temp
                if subkey:
                    self.data[key][subkey] = value
                else:
                    self.data[key].[len(self.data[key])] = value
            else:
                if subkey:
                    self.data[key][subkey] = value
                else:
                    self.data[key].[len(self.data[key])] = value
        else:
            self.data[key] = value
