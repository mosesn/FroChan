import cherrypy
import cgi #For html escaping

MAX = 10

def divify(some_list):
    return ["<div>%s</div>" % s for s in some_list]

class Form:
    def __init__(self):
        self.inputs = list()
        self.action = ""
        self.method = ""
        self.submit = None

    def add_input(self, name, tpe):
        self.inputs.append((name, tpe))

    def add_submit(self, name="", value):
        self.submit = (name, value)

    def set_action(self, action):
        self.action = action

    def set_method(self, method):
        self.method = method

    def __str__(self):
        inputs = ["""<input type="%s" name="%s" />""" % (name, tpe) for (name, tpe) in self.inputs]
        if self.submit:
            inputs.append("""<input name="%s" type="submit" value="%s" />""" % self.submit)
                          
        form = """<form action="%s" method="%s">%s</form>""" % (self.action, self.method, "".join(inputs))
        return form
                          
class MessageBoard:
    
    def __init__(self):
        self.pageviews = 0
        self.comments = list()
        
    def index(self, comment=""):
        errors = list()
        
        #Validate user input:
        if cherrypy.request.method == "POST":
            if not comment:
                errors.append("Your comment is empty.")
            else:
                #Prepend
                self.comments.reverse()
                self.comments.append(cgi.escape(comment))
                self.comments.reverse()
                #Truncate
                self.comments = self.comments[:MAX]

            
        #form = \
        #     """
        #    <form action="" method="post">
        #        <input type="text" name="comment" />
        #        <input type="submit" value="Submit" />
        #    </form>
        #    """
        form = Form()
        form.set_action
        errs = "<br />".join(errors)
        output = "<br />".join(divify(self.comments))
        return "<br />".join([errs, form, output])
        

    index.exposed = True

        
cherrypy.quickstart(MessageBoard())
