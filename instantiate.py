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

collectionObjs = RDFqueries(collectionPID)


#using RDF predicate "isPartOf", indicating the location of the Omeka script    
risearch_query = "select $subject from <#ri> where <info:fedora/{pid}> <info:fedora/fedora-system:def/relations-external#isPartOf> $subject".format(pid=collectionObjs[0])
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
for collection in iterLines:            
    omekaCollection = collection.strip()


try:
    print omekaCollection
except:
    print "Omeka collection not found."





