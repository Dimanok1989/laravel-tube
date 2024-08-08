from pytubefix import YouTube
from pytubefix.cli import on_progress
import sys

yt = YouTube(sys.argv[1], on_progress_callback = on_progress)

stream = yt.streams.get_by_itag(sys.argv[3])
print(stream.download(sys.argv[2], sys.argv[4]))
