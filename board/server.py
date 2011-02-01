import cherrypy

#Server configuration
site_conf = \
    {'server.socket_host': '127.0.0.1',
     'server.socket_port': 80,
     #hitting the server at the url /static will ask the server to serve static content
     '/static': {
            'tools.staticdir.on': True,
            #'static' is the local path to the static directory
            'tools.staticdir.dir': 'static'
         }
    }

cherrypy.config.update(site_conf)

#App configuration
board_conf = \
    {
    #The key 'database' is for book keeping and as of now doesnt effect anything
    'database': 
        {'type': 'mongodb',
         'host': 'localhost',
         'port': 0000,
        }
    }


class Board:
    def index(self):
        return "This is the placeholder for the board."
    index.exposed = True

cherrypy.tree.mount(Board(), '/', board_conf)

cherrypy.engine.start()
