import cherrypy
import pymongo
from pymongo import Connection

class HelloWorld:
    
    def __init__(self):
        self.pageviews = 0
        self.comments = list()
        connection = Connection('localhost',27017)
        self.db = connection.post_database
        
    def index(self):
        form = \
             """
            <form action="/post" method="post">
                <input type="text" name="comment" />
                <input type="submit" value="Submit" />
            </form>
            """

        outputs = ""
        posts=self.db.posts
        for post in posts.find():
            outputs += "%s<br/>" % post["comment"]
            
        return form + "%s" % str(outputs)

    def post(self, comment = ""):
        if not comment:
            return "Wow don't post empty comments!"
        else:
            posts=self.db.posts
            posts.insert({"comment" : comment})
            return """Comment posted successfully! <a href="\">"""
        

    index.exposed = True
    post.exposed = True
    
cherrypy.quickstart(HelloWorld())
