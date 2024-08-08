import sys
from pytubefix import YouTube
from pytubefix.cli import on_progress
import json

youtube = YouTube(sys.argv[1], on_progress_callback = on_progress)

response = {
    "type": "YouTube",
    "title": youtube.title,
    "thumbnail_url": youtube.thumbnail_url,
    "author":  youtube.author,
    "channel_id": youtube.channel_id,
    "channel_url": youtube.channel_url,
    "description": youtube.description,
    "keywords": youtube.keywords,
    "length": youtube.length,
    "publish_date": youtube.publish_date.strftime('%Y-%m-%d %H:%M:%S %z'),
    "streams": [],
}

for stream in youtube.streams:

    item = {
        "itag": stream.itag if hasattr(stream, 'itag') else None,
        "mime_type": stream.mime_type if hasattr(stream, 'mime_type') else None,
        "audio_codec": stream.audio_codec if hasattr(stream, 'audio_codec') else None,
        "type": stream.type if hasattr(stream, 'type') else None,
        "only_video": stream.includes_video_track if hasattr(stream, 'includes_video_track') else None,
        "only_audio": stream.includes_audio_track if hasattr(stream, 'includes_audio_track') else None,
        "filesize": stream.filesize if hasattr(stream, 'filesize') else None,
    }

    if stream.type == "video":
        item["res"] = stream.resolution if hasattr(stream, 'resolution') else None
        item["fps"] = stream.fps if hasattr(stream, 'fps') else None
        item["video_codec"] = stream.video_codec if hasattr(stream, 'video_codec') else None
    elif stream.type == "audio":
        item["abr"] = stream.abr if hasattr(stream, 'abr') else None

    response["streams"].append(item)

print(json.dumps(response))
