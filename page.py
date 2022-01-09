import urllib.request

page = "page=discord&subpage=alarm"
#pageWithMsg = "index.php?page=discord&msg=jesuissurleserveur"
fp = urllib.request.urlopen("http://gt.lacakaouette.fr/index.php?" + page)
mybytes = fp.read()

mystr = mybytes.decode("utf8")
fp.close()

print(mystr)