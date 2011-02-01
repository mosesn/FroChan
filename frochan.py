import pymongo
import cherrypy
from pymongo import Connection
import cgi #For html escaping

class Post:

    #inits
    def __init__(self,collection,dic):
        self.message=dic['message']
        self.replies=dic['replies']
        self.collection=collection
        if dic['_id']==None:
            self.id=self.collection.insert({'message':self.message,'replies':self.replies,'timestamp':datetime.datetime.utcnow()})
        else:
            self.id=dic['_id']

    #refreshes the state of the post
    def update(self):
        coll_dict=self.collection.findOne('_id':self.id)
        self.replies=coll_dict['replies']
        self.message=coll_dict['message']

    #adds a reply atomicly
    def add_reply(self,new_reply):
        self.replies.append(new_reply)
        self.collection.update({'_id'=self.id},{"$set": "replies":self.replies,"timestamp":datetime.datetime.utcnow()})

class MessageBoard:
    def index:
        pass
