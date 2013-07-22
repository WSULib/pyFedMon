import time
import re
import urllib, urllib2
from sensitive import *
import time
import sys
from lxml import etree


#get target collection
try:
	collectionPID = sys.argv[1]
	print "Collection PID:",collectionPID
except:
	print "Please enter a collection PID as argument.  Exiting now."
	exit()

#instantiate fedObjs array
collectionObjs = []

#Get objects from argument PID
def RDFqueries(collectionPID):
    #using RDF predicate "isPartOf", indicating the location of the Omeka script    
    risearch_query = "select $object from <#ri> where $object <info:fedora/fedora-system:def/relations-external#isMemberOfCollection> <info:fedora/{collectionPID}>".format(collectionPID=collectionPID)    
    risearch_params = urllib.urlencode({
        'type': 'tuples', 
        'lang': 'itql', 
        'format': 'CSV',
        'limit':'',
        'dt': 'on', 
        'query': risearch_query
        })
    risearch_host = "http://{username}:{password}@localhost/fedora/risearch?".format(username=username,password=password)
    collections = urllib.urlopen(risearch_host,risearch_params)       
    iterLines = iter(collections)  
    next(iterLines)  
    for PID in iterLines:        
        collectionObjs.append(PID.split("/")[1].strip())

    print collectionObjs
    return collectionObjs


#Get DC datastream, write to CSV file
def writeObjRow(fhand, PID):

	#namespace map
	nsmap = {'dc': 'http://purl.org/dc/elements/1.1/', 'oai_dc': 'http://www.openarchives.org/OAI/2.0/oai_dc/'}


	#create array of tuples, e.g. ('title','horse race')
	urlstring = "http://{username}:{password}@localhost/fedora/objects/{PID}/datastreams/DC/content".format(PID=PID,username=username,password=password)
	# print urlstring
	response = urllib.urlopen(urlstring)	
	DC = response.read()
	print DC
	DCroot = etree.fromstring(DC)

	#WORKING
	# title = DCroot.xpath('//dc:title', namespaces=nsmap)[0]
	# print title.text

	elements = DCroot.xpath('//oai_dc:dc', namespaces=nsmap)[0]	
	#use .title() to cap first letter
	# for each in elements:
	# 	tag_name = each.tag.split("}")[1].title()
	# 	print "Dublin Core: "+tag_name
		
	




#Go time.
collectionObjs = RDFqueries(collectionPID)
fhand = open("testing.csv",'w')
for PID in collectionObjs:
	writeObjRow(fhand, PID)
fhand.close()
