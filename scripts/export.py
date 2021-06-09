import sys
import json
from pymongo import MongoClient
from pathlib import PurePath
import gridfs
from bson.objectid import ObjectId
from settings import *

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
