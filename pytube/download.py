import sys
from pytubefix import YouTube
from pytubefix.cli import on_progress

yt = YouTube(sys.argv[1], on_progress_callback = on_progress)

try:
    stream = yt.streams.get_by_itag(sys.argv[3])
except Exception as e:
    print(str(e))
    exit(1)

try:
    print(stream.download(sys.argv[2], sys.argv[4]))
except Exception as e:
    print(str(e))
    exit(1)
