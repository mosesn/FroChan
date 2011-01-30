import cherrypy
import pymongo
from pymongo import Connection
import cgi #For html escaping

MAX = 10

def divify(some_list):
    return ["<div>%s</div>" % s for s['comment'] in some_list]

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
    
    def __init__(self):
        self.pageviews = 0
        connection = Connection('localhost',27017)
        self.db = connection.post_database
        
    def index(self, comment=""):
        errors = list()
        
        posts=self.db.posts

        #Validate user input:
        if cherrypy.request.method == "POST":
            if not comment:
                errors.append("Your comment is empty.")
            else:
                #Prepend
                posts.insert({'comment' : cgi.escape(comment)})
                #Truncate
                posts = posts[:MAX]

            
        form = Form()
        form.set_method("post")
        form.add_input("comment", "text")
        form.add_submit()
        
        errs = "<br />".join(errors)
        output = "<br />".join(divify(posts.find()))
        return "<br />".join([errs, str(form), output])

    index.exposed = True

        
cherrypy.quickstart(MessageBoard())
