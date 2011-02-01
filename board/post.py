import pymongo
import cherrypy
from pymongo import Connection
import cgi #For html escaping
import datetime

class Post:
    #inits
    def __init__(self,board,post_id="",message=""):
        connection=Connection()
        #name of database
        db=connection.posts_database
        self.collection=db.posts
        self.board=board
        if post_id == "":
            self.timestamp=datetime.datetime.utcnow()            
            self.post_id=self.collection.insert({'message':message,'board':self.board,'replies':[],'timestamp':self.timestamp})
        else:
            dic=self.collection.findOne({'_id':post_id})
            self.message=dic['message']
            self.replies=dic['replies']
            self.post_id=dic['_id']

    #refreshes the state of the post
    def update(self):
        coll_dict = self.collection.findOne({'_id':self.post_id})
        self.replies = coll_dict['replies']
        self.message = coll_dict['message']
        self.timestamp = coll_dict['timestamp']

    #adds a reply atomicly
    def add_reply(self,new_reply):
        self.timestamp = datetime.datetime.utcnow()
        self.replies.append(new_reply)
        self.collection.update({'_id' : self.post_id},{"$set" : {"replies" :self.replies,"timestamp":self.timestamp}})

    def get_timestamp():
        return self.timestamp

def get_posts(board):
    connection=Connection()
    db=connection.posts_database
    posts=db.posts
    return list(posts.find({'board':board}))
