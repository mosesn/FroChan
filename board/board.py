#This class connects the backend with the frontend by accessing data and filling the templates
class Board:
    def __init__(self):
        #Connect and load posts
        self.posts = load()
        #load templates
        self.expand_template = ""
        self.index_template = ""

    def index(self):
        name_space = {'posts':self.post}
        return str(Template(self.index_template, name_space))
        
    def expand(self, post_id):
        #display the post and responses for post_id
        post = Post(post_id)
        #Render the page
        name_space = {'post':post}
        return str(Template(self.expand_template, name_space))
