import sys
import json
from pymongo import MongoClient
from pathlib import PurePath
import gridfs
from bson.objectid import ObjectId
from settings import *

'''
60a648930bb674.00094474,60a64a9f956699.64250026,60a617b6ecc358.87040084,60a0170e7b9041.47453401,60a018efc21a50.60836011,60a2eb0061f600.54953893,60a61568794803.96902889,60ae396178b381.39706794
'''

# connect to DB
client = MongoClient('mongodb://%s:%s@%s/?retryWrites=false' % (username, password, host))
db = client[dbname]
representations = db['representations']
fs = gridfs.GridFS(db)

if len(sys.argv) > 1:
    list = sys.argv[1].split(',')
else:
    raise SystemExit('No projects to export')
    
print()

json_array = []
for rep in representations.find({ "_id": { "$in": list } }):
    json_array.append(rep)
    print(rep['_id'])
    
    for f in rep['files']:
        cursor = fs.find({ '_id': ObjectId(f['id']) })
        
        i = 0
        while(i < cursor.count()):
            fi=cursor.next()
            print('saving file', fi.filename)
            path = export_path + PurePath(fi.filename).name
            with open(path,"wb") as f:
                f.write(fi.read())
                i=i+1

    print()

print("Saving JSON representations file")
with open(export_path + 'representations.json', 'w') as outfile:
    json.dump(json_array, outfile)
    #json.dump(json_array, outfile, indent=4)
