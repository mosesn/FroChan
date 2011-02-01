import cherrypy
import os
import sys
base = os.path.dirname(sys.argv[0])

#Server configuration
site_conf = \
    {'server.socket_host': '127.0.0.1',
     'server.socket_port': 80,
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
        },
    '/static':
        {'tools.staticdir.on':True,
        'tools.staticdir.dir':os.path.join(base, 'static'),
         'tools.staticdir.content_types': {'png': 'image/png',
                                           'css': 'text/css',
                                           'js':'application/javascript',}
         }
    }

class Board:
    def index(self):
        return "This is the placeholder for the board."
    index.exposed = True
    
cherrypy.quickstart(Board(), '/', board_conf)
