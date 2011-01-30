import cherrypy
import pymongo
from pymongo import Connection
import cgi #For html escaping

MAX = 10
def divify(some_list):
    return ["<div>%s</div>" % s['post_content'] for s in list(some_list)[:(-MAX-1):-1]]

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

        posts_list= posts.find()
        #Validate user input:
        if cherrypy.request.method == "POST":
            if not comment:
                errors.append("Your comment is empty.")
            else:
                #Prepend
                post=Post({'post_content' : cgi.escape(comment),
                              'comments':[]})
                posts.insert(post.get_dict())
                #Truncate
                posts_list = posts.find()

            
        form = Form()
        form.set_method("post")
        form.add_input("comment", "text")
        form.add_submit()
        
        errs = "<br />".join(errors)
        output = "<br />".join(divify(posts_list))
        return "<br />".join([errs, str(form), output])

    index.exposed = True
        
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

    #Get comments preview
    def get_comments_preview(self):
        first_three_comments = ["""<div id="%s_%i">%s</div>""" % (self.id, i, self.comments[i][:100]) for i in min(3, len(self.comments)-1)]
        return "".join(first_three_comments)
                                
    #Get html preview
    def get_html_preview(self):
        """<div id="%s">%s</div>""" % (self.id, self.post_content, self.get_comments_preview)

        
cherrypy.quickstart(MessageBoard())
