import cherrypy
from Cheetah.Template import Template
from post import Post
import post as p

#This class connects the backend with the frontend by accessing data and filling the templates
class Board:
    def __init__(self):
        self.board_name = "FroSci"
        #Connect and load posts
        self.posts = p.get_posts(self.board_name)
        #load templates
        index_t = open('templates/index.tmpl', 'r')
        expand_t = open('templates/expand.tmpl', 'r')
        #string-ize
        self.expand_template = expand_t.read()
        self.index_template = index_t.read()

    def index(self, message=None):
        self.posts = p.get_posts(self.board_name)
        if cherrypy.request.method == 'POST':
            if not message:
                pass #Add to errors or something...
            else:
                print("Adding %s as a post" % message)
                post = Post(self.board_name, message=message)
        
        name_space = {'posts':self.posts}
        return str(Template(self.index_template, name_space))
    index.exposed = True
    
    def expand(self, post_id, message=None):
        self.posts = p.get_posts(self.board_name)
        if cherrypy.request.method == 'POST':
            if not message:
                pass #Add to errors or something...
            post = Post(self.board_name, message=message)
        #display the post and responses for post_id
        post = Post(self.board_name, post_id=post_id)
        #Render the page
        name_space = {'post':post}
        return str(Template(self.expand_template, name_space))
    expand.exposed = True
