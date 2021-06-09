import datetime
import shutil
from pymongo import MongoClient
from pathlib import Path
import gridfs
from settings import *

# connect to DB
client = MongoClient('mongodb://%s:%s@%s/?retryWrites=false' % (username, password, host))
db = client[dbname]
representations = db['representations']
fs = gridfs.GridFS(db)

for rep in representations.find({ 
    "$expr": {
        "$lte": [ { "$dateFromString": { "dateString": "$projectSettings.expiration.date" }}, datetime.datetime.now() ]
    }
 }):
    
	id = rep['_id']

	if rep['projectSettings']['status'] == 'w':
		print('####')
		print('Remove files:', id, rep['projectSettings']['status'], rep['projectSettings']['expiration']['date'])

		for f in rep['files']:
			# 1) remove trajectory if it exists
			if f['trajectory']:
				path = disk_path + id
				if len(id) == 23 and path != disk_path and Path(path).exists():
					shutil.rmtree(path)
					print('  removing trajectory ' + path)
			# 2) remove file from grid
			#file = fs.find_one({ 'metadata.project_id': id })
			file = fs.find_one({ '_id': ObjectId(f['id']) })
			if file:
				fs.delete(file._id)
				print('  removing fs file ' + str(file._id))

		# 3) remove job from DB
		representations.delete_one( { '_id': id })
		print('  removing document ' + id)

		print('####')
		print('')

	if rep['projectSettings']['status'] == 'wf':

		print('####')
		print('Remove only document:', id, rep['projectSettings']['status'], rep['projectSettings']['expiration']['date'])

		# 1) remove job from DB
		representations.delete_one( { '_id': id })
		print('  removing document ' + id)

		print('####')
		print('')