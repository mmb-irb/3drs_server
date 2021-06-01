import shutil
import sys
from pymongo import MongoClient
from pathlib import Path, PurePath
import gridfs
from bson.objectid import ObjectId
from settings import *

'''
60a60efd239cb3.73083634,60a616d3288258.23242408,60a61568794803.96902889,60a617b6ecc358.87040084,60a6433d5fb7a4.22415465,60a644f252dbe2.54036956,60a648930bb674.00094474,60a64a9f956699.64250026,60accc82c429b3.25791364,60acde3f4aaed5.62066121,60accd9971d5f5.37603725,60accd60125068.80880557,60acceaa75a914.38897720,60acd733a3ac78.56692937,60acd6d8bac127.66228577,609f864ae5f988.76031858,609f8bf52bba88.95483112,60a0170e7b9041.47453401,60a018efc21a50.60836011,60a2eb0061f600.54953893,60a53d098b0603.96200952,60ae396178b381.39706794,60b50467e32fa4.94941412
'''

# connect to DB
client = MongoClient('mongodb://%s:%s@%s/?retryWrites=false' % (username, password, host))
db = client[dbname]
representations = db['representations']
fs = gridfs.GridFS(db)

if len(sys.argv) > 1:
    list = sys.argv[1].split(',')
else:
    print('Removing all')

c = 0
for rep in representations.find({ }):
    if rep['_id'] not in list:

        id = rep['_id']

        if rep['projectSettings']['status'] == 'w':
            print('####')
            print('Remove files:', id, rep['projectSettings']['status'], rep['projectSettings']['uploadDate']['date'])

            for f in rep['files']:
                # 1) remove trajectory if it exists
                if f['trajectory']:
                    path = disk_path + id
                    if len(id) == 23 and path != disk_path and Path(path).exists():
                        shutil.rmtree(path)
                        print('  removing trajectory ' + path)
                # 2) remove file from grid
                file = fs.find_one({ '_id': ObjectId(f['id']) })
                if file:
                    fs.delete(file._id)
                    print('  removing fs file ' + str(file._id))

            # 3) remove job from DB
            representations.delete_one( { '_id': id })
            print('  removing document ' + id)

            print('####')
            print('')

        else:

            print('####')
            print('Remove only document:', id, rep['projectSettings']['status'], rep['projectSettings']['uploadDate']['date'])

            # 1) remove job from DB
            representations.delete_one( { '_id': id })
            print('  removing document ' + id)

            print('####')
            print('')

        c = c + 1

print("Total removed: %d" % c)