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

#globals
collectionObjs = []

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
totalCount = len(collectionObjs)



#Get Objects/Datastreams modified on or after this date
def RDFqueries(eventInfo):
    #using RDF predicate "isPartOf", indicating the location of the Omeka script    
    risearch_query = "select $subject from <#ri> where <info:fedora/{pid}> <info:fedora/fedora-system:def/relations-external#isPartOf> $subject".format(pid=str(eventInfo['pid']))
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
        eventInfo['omekaCollection'] = collection.strip()


    #get identifier
    risearch_query = "select $subject from <#ri> where <info:fedora/{pid}> <http://purl.org/dc/elements/1.1/identifier> $subject".format(pid=str(eventInfo['pid']))
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
    
    #BACKUP
    iterLines = iter(collections)  
    next(iterLines)  
    for identifier in iterLines:
        identifier_string = re.sub('"','',identifier).strip()         
        if identifier_string.startswith("wayne:"):
            eventInfo['dc_identifier'] = identifier_string

    #return updated dictionary
    return eventInfo


def sendJSONping(eventInfo):        
    data = urllib.urlencode(eventInfo)     
    # Send HTTP POST request
    try:
        request = urllib2.Request(eventInfo['omekaCollection'], data)     
        response = urllib2.urlopen(request)      
        html = response.read()             
        print html
    except:
        print "The POST request could not be made."


#Go Time.
#TESTING
# collectionObjs = ['wayne:MOTA_PH_19851986_1t_081', 'wayne:MOTA_PH_19791980_3r_001', 'wayne:MOTA_PH_19831984_3t_005', 'wayne:MOTA_PH_19831984_3t_006', 'wayne:MOTA_PH_19761977_1r_010']
count = 1
for pid in collectionObjs:
    print "Working on",count,"of",totalCount
    eventInfo = {}
    eventInfo['pid'] = pid
    eventInfo['type'] = "initialize"
    eventInfo = RDFqueries(eventInfo)
    print "Sending the following JSON ping:\n",eventInfo    
    sendJSONping(eventInfo)
    count = count + 1    
    # time.sleep(5)      

