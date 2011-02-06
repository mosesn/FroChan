import pymongo
import cherrypy
from pymongo import Connection
import cgi #For html escaping
import time
from datetime import datetime
from pymongo.objectid import ObjectId

num_saved=25

class Post:
    #inits
    def __init__(self,board,post_id="",message=""):
        userID = 'admin'
        pwd = 'hackcu11'
        host= 'flame.mongohq.com'
        port = 27039
        dbName = 'posts_database'
        connection=Connection(host,port)
        #name of database
        db = connection[dbName]
        db.authenticate(userID, pwd)

        self.collection=db.posts
        self.board=board
        if post_id == "":
            self.timestamp=time.time()
            self.post_id=self.collection.insert({'message':cgi.escape(message),'board':self.board,'replies':[],'timestamp':self.timestamp})
            tmp=self.collection.find().sort('timestamp')
            tmp=(list(tmp))
            if(len(tmp)>num_saved):
                self.collection.remove({'_id':tmp[0]['_id']})
            self.replies=[]
        else:
            #needs ObjectId to string->weird objectid type for mongo
            dic = db.posts.find_one({'_id': ObjectId(post_id) })
            self.message=dic['message']
            self.replies=dic['replies']
            self.post_id=dic['_id']
        self.min_repls=self.get_mini_replies()


    #refreshes the state of the post
    def update(self):
        coll_dict = self.collection.find_one({'_id': self.post_id})
        self.replies = coll_dict['replies']
        self.message = coll_dict['message']
        self.timestamp = coll_dict['timestamp']

    def get_mini_replies(self):
        return self.replies[:3]

    #adds a reply atomicly
    def add_reply(self,new_reply):
        self.timestamp = time.time()
        self.replies.append({'reply-text':cgi.escape(new_reply),'timestamp':datetime.now()})
        self.collection.update({'_id' : self.post_id},{"$set" : {"replies" :self.replies,"timestamp" : self.timestamp}})

    def get_timestamp():
        return self.timestamp

def get_posts(board):
    host= 'flame.mongohq.com'
    port = 27039
    dbName = 'posts_database'
    connection=Connection(host,port)
    userID = 'admin'
    pwd = 'hackcu11'

    #name of database
    db = connection[dbName]
    db.authenticate(userID, pwd)
    posts=db.posts
    #print [post for post in posts.find({'board':board})]
    print [str(p['_id']) for p in posts.find({'board':board})] 
    return [Post(board,post_id=post['_id']) for post in posts.find({'board':board})][::-1]
