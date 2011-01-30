import cherrypy

class HelloWorld:
    
    def __init__(self):
        self.pageviews = 0
        self.comments = list()
        
    def index(self):
        form = \
             """
            <form action="/post" method="post">
                <input type="text" name="comment" />
                <input type="submit" value="Submit" />
            </form>
            """

        outputs = ""
        for comment in self.comments:
            outputs += "%s<br/>" % comment
            
        return form + "%s" % str(outputs)

    def post(self, comment = ""):
        if not comment:
            return "Wow don't post empty comments!"
        else:
            self.comments.append(comment)
            return """Comment posted successfully! <a href="\">"""
        

    index.exposed = True
    post.exposed = True
    
cherrypy.quickstart(HelloWorld())
