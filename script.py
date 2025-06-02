import sys

try:
    print(sys.argv[1])
    my_file = open("testFileFromPython.txt", "w+")
    my_file.write(sys.argv[1])
    my_file.close()
except Exception as e:
    print(e)
