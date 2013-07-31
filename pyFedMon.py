import time
import re
import urllib, urllib2
from sensitive import *
import time

#open log file for monitoring
log_file = "/usr/local/fedora/server/logs/fedora.log"
fhand = open("{log_file}".format(log_file=log_file),'r')
fhand.seek(0,2)

#list of API calls to listen for, a positive match runs that function from felogMonitor()
methodListen = ['modifyDatastreamByValue', 'modifyDatastreamByReference']


#globals
JSONping = {}

#monitor, functions run when terms are found
#e.g. globals()[goober]()
# {'omekaCollection': 'http://[IPADDRESS]/tmp/omeka_connector.php', 'datastreamId': 'DC', 'pid': 'fedomeka:3', 'dc_identifier': 'fedomeka:3', 'altID': 'omekaObj'}

def fedlogMonitor():
    print "Monitoring "+log_file+"...\n"
    while True:
        where = fhand.tell()
        line = fhand.readline()
        #keep sleeping, keep waiting
        if not line:
            time.sleep(1)
            fhand.seek(where)        
        
        #check methodListen[] for method
        else:
            eventInfo = {}      
            # get altIDs (must be "omekaObj" at this point)
            try:
                temp_altID = re.search('altIDs: (.+?),', line).group(1)
                eventInfo['altID'] = re.sub("'","",temp_altID)
            except:
                eventInfo['altID'] = False            
            # if omekaObj, begin tests                
            if eventInfo['altID'] == "omekaObj":
                print "Omeka Object detected, determining Fed method..."
                #get DS
                eventInfo['pid'] = re.search('pid: (.+?),', line).group(1)
                eventInfo['datastreamId'] = re.search('datastreamId: (.+?),', line).group(1)
                for method in methodListen:                                   
                    if line.find(method) > 0 :
                        print "Fedora method:",method                        
                        time.sleep(3)  #CHANGE THIS IF CHANGES NOT REFLECTED, WAITING ON MULGARA XML DATABASE TO UPDATE                                     
                        globals()[method](eventInfo)            
            
               

#utilities
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
    iterLines = iter(collections)  
    next(iterLines)  
    for identifier in iterLines:
        identifier_string = re.sub('"','',identifier).strip()         
        if identifier_string.startswith("wayne:"):
            eventInfo['dc_identifier'] = identifier_string

    #return updated dictionary
    return eventInfo

#API FUNCTIONS (all passed with eventInfo dictionary)
def modifyDatastreamByValue(eventInfo):    
    #Hit risearch, get Omeka collection name / location    
    eventInfo = RDFqueries(eventInfo)    
    print "Sending the following JSON ping:\n",eventInfo
    sendJSONping(eventInfo)    


#API FUNCTIONS (all passed with eventInfo dictionary)
def modifyDatastreamByReference(eventInfo):    
    #Hit risearch, get Omeka collection name / location    
    eventInfo = RDFqueries(eventInfo)    
    print "Sending the following JSON ping:\n",eventInfo
    sendJSONping(eventInfo)  


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


#Go time.
fedlogMonitor()
