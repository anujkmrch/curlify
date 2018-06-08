# curlify
making curl request from php and python script, I build curlify class as a fun project, with the help of this class you can make api calls from php script

## How to use
Primarily, I build it using with the command line interface (CLI) but you can also use it within your web application.
To use this class, please have a look at the record file for reference. in my case record.php or record.py is the main file which initialize curlify object and sets the url, data and other parameters.

This class is wonderful for making remote api call for developing  a SDK for rest api based service. all you need to send the data and set the parameter.

Most of the modern rest api provider use json string as reply you might want to use json_decode function in php to convert json string to array or object to make useful in your applications.

The limit of using this class depends on you what you can think of.
According to me "SKY IS THE LIMIT"

## Note**
Currently it does support only for text based data, I will integrate the file upload feature also for it.

You can contact me on facebook https://facebook.com/anujkch or me@anujkch.com
