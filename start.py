import os
import sys
import cherrypy
import pymongo
from pymongo import Connection
from pymongo.objectid import ObjectId
import cgi #For html escaping

base = os.path.dirname(sys.argv[0])
print(base)

MAX = 10

TEMPLATE = """
<html>
<head>
<style type=text/css>
    body {
        font-family:Georgia,Serif;
    }
  
    .post {
      background: #F5F5F5;
      border: 1px solid black;
      padding: 10px;
      margin:0px auto;
      width: 50%%;
    }

    .response{
    width: 70%%;
    background:#444444;
    color:#FFFFFF;
    padding: 4px 20px;
    margin: 5px 5px 5px 5px;
    }
    
</style>
</head>
<body>
<img src="/static/sweetie.png" />
%s
</body>
</html>
"""

class Form:
    def __init__(self):
        self.inputs = list()
        self.action = ""
        self.method = ""
        self.submit = None

    def add_input(self, name, tpe):
        self.inputs.append((name, tpe))

    def add_submit(self, name="", value="Submit"):
        self.submit = (name, value)

    def set_action(self, action):
        self.action = action

    def set_method(self, method):
        self.method = method

    def __str__(self):
        inputs = ["""<input name="%s" type="%s" />""" % (name, tpe) for (name, tpe) in self.inputs]
        if self.submit:
            inputs.append("""<input name="%s" type="submit" value="%s" />""" % self.submit)
                          
        form = """<form action="%s" method="%s">%s</form>""" % (self.action, self.method, "".join(inputs))
        return form
                          
class MessageBoard:
    _cp_config = {'tools.staticdir.on' : True,
                  'tools.staticdir.dir' : '/path/to/your/static/file/directory',
                  'tools.staticdir.index' : 'index.html',
    }    
    def __init__(self):
        self.pageviews = 0
        connection = Connection('localhost',27017)
        self.db = connection.post_database
    
    def index(self, comment=""):
        errors = list()
        
        posts=self.db.posts

        posts_list= posts.find()
        #Validate user input:
        if cherrypy.request.method == "POST":
            if not comment:
                errors.append("Your comment is empty.")
            else:
                #Prepend
                posts.insert({'post_content' : cgi.escape(comment), 'comments':[]})
                #Truncate
                posts_list = posts.find()
        
        form = Form()
        form.set_method("post")
        form.add_input("comment", "text")
        form.add_submit()
        
        errs = "<br />".join(errors)

        #Generate previews for the last MAX posts
        def preview_posts(some_list):
            return "<br />".join([Post(s).get_html_preview() for s in list(some_list)[:(-MAX-1):-1]])
        
        output = preview_posts(posts_list)
        return TEMPLATE % "<br />".join([errs, str(form), output])

    def expand(self, post_id, comment=""):
        errors = list()
        posts = self.db.posts
        
        my_doc = posts.find_one({'_id': ObjectId(post_id)})
        
        #Validate user input:
        if cherrypy.request.method == "POST":
            if not comment:
                errors.append("Your comment is empty.")
            else:
                posts.update(my_doc, {"$push": {"comments" : comment}})
        
        form = Form()
        form.set_method("post")
        form.set_action("")
        form.add_input("comment", "text")
        form.add_submit()

        post = Post(posts.find_one({'_id': ObjectId(post_id)}))
        return TEMPLATE % ("<br />".join(errors) + str(form) + post.get_html())
        

    index.exposed = True
    expand.exposed = True
        
class Post:
    #{"post_content": string, "comments": list}
    def __init__(self, dic):
        if dic:
            self.load(dic)
            self.id=dic['_id']

    #Make the contents of this Post match the dictionary
    def load(self, dic):
        self.post_content = dic["post_content"]
        self.comments = dic["comments"]

    #Add a comment
    def add_comment(self, comment):
        self.comments.append(comment)

    #Get a dictionary representation of this Post
    def get_dict(self):
        return {"post_content": self.post_content, "comments": self.comments}

    #Get comments preview html
    def get_comments_preview(self):
        first_three_comments = ["""<div id="%s_%i" class="response">%s</div>""" % (self.id, i, self.comments[i][:100]) for i in range(min(3, len(self.comments)))]
        return "".join(first_three_comments)
    
    #Get comments full html
    def get_comments_html(self):
        comments = ["""<div id="%s_%i" class="response">%s</div>""" % (self.id, i, self.comments[i]) for i in range(len(self.comments))]
        return "".join(comments)
    
    #Get html preview
    def get_html_preview(self):
        return """<div id="%s" class="post">%s%s%s</div>""" % (self.id, self.post_content[:100], self.get_comments_preview(), self.get_link())

    #Get html full
    def get_html(self):
        return """<div id="%s" class="post">%s%s</div>""" % (self.id, self.post_content, self.get_comments_html())

    #get link to view post
    def get_link(self):
        return """<a href="/expand/%s/">read</a>""" % str(self.id)

conf = {'/static':
        {'tools.staticdir.on':True,
        'tools.staticdir.dir':os.path.join(base, 'static'),
         'tools.staticdir.content_types': {'png': 'image/png',
                                           'css': 'text/css',
                                           'js':'application/javascript',}
         }
    }
        
cherrypy.quickstart(MessageBoard(), '/', config=conf)
