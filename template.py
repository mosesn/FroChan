import sys
from Cheetah.Template import Template

class a:
    pass

f = open('template.txt', 'r')
template = f.read()

#post---+
#       +-----comment
#       +-----post_id
#       +-----replies---+
#                       +--comment
#                       +--index0
#

post1 = a()
post2 = a()
post3 = a()

post1.content = "content1"
post1.post_id = 1
post1.replies = []
post2.content = "content2"
post2.post_id = 2
post2.replies = []
post3.content = "content3"
post3.post_id = 3
post3.replies = []

reply = a()
reply.content = "Hey!"
post3.replies.append(reply)


posts = [post1, post2, post3]

a = a()
a.hello = "hello!"
a.wow = "wow"
name_space = {'title':"The Title of My Page", "posts":posts}
t = Template(template, searchList=[name_space])
print(t)
