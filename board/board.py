import cherrypy
from Cheetah.Template import Template
from post import Post
import post

#This class connects the backend with the frontend by accessing data and filling the templates
class Board:
    def __init__(self):
        #Connect and load posts
        self.posts = post.get_posts()
        #load templates
        index_t = open('templates/index.tmpl', 'r')
        expand_t = open('templates/expand.tmpl', 'r')
        #string-ize
        self.expand_template = expand_t.read()
        self.index_template = index_t.read()

    def index(self, message=None):
        if cherrypy.request.method == 'POST':
            if not message:
                pass #Add to errors or something...
            post = Post(message=message)
        
        name_space = {'posts':self.posts}
        return str(Template(self.index_template, name_space))
    index.exposed = True
    
    def expand(self, post_id, message=None):
        if cherrypy.request.method == 'POST':
            if not message:
                pass #Add to errors or something...
            post = Post(message=message)
        #display the post and responses for post_id
        post = Post(post_id=post_id)
        #Render the page
        name_space = {'post':post}
        return str(Template(self.expand_template, name_space))
    expand.exposed = True
