import cherrypy
from Cheetah.Template import Template
from post import Post
import post as p
from datetime import datetime

#This class connects the backend with the frontend by accessing data and filling the templates
class Board:
    def __init__(self,board_name="FroSci"):
        self.board_name = board_name
        #Connect and load posts
        self.posts = p.get_posts(self.board_name)
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
            else:
                print("Adding %s as a post" % message)
                self.add_post(message)
        self.posts = p.get_posts(self.board_name)
        name_space = {'posts':self.posts}
        return str(Template(self.index_template, name_space))
    index.exposed = True

    def clas(self, board ,message=None):
        if cherrypy.request.method == 'POST':
            if not message:
                print('No message received')
                pass #Add to errors or something...
            else:
                #adds a post
                self.add_post(message)

        #Connect and load posts
                self.posts = p.get_posts(self.board_name)
        #load templates
                index_t = open('templates/index.tmpl', 'r')
                expand_t = open('templates/expand.tmpl', 'r')
        #string-ize
                self.expand_template = expand_t.read()
                self.index_template = index_t.read()

        self.board_name = board

        self.posts = p.get_posts(self.board_name)

        name_space = {'posts':self.posts}
        return str(Template(self.index_template, name_space))
    clas.exposed = True
    
    def expand(self, post_id, message=None):
        post = Post(self.board_name, post_id=post_id)
        if cherrypy.request.method == 'POST':
            if not message:
                print('Message was empty')
                pass #Add to errors or something...
            else:
                post.add_reply(message)
        post.update()
        #display the post and responses for post_id
        post = Post(self.board_name, post_id=post_id)
        #Render the page
           
        name_space = {'post':post}
        return str(Template(self.expand_template, name_space))
    expand.exposed = True

    def add_post(self,message):
        post = Post(self.board_name, message=message)
        
