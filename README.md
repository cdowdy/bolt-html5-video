# Bolt HTML5 Video Extension

A Bolt Extension that allows you to play local videos either through ```/files/``` or from your own CDN.

Has current support for:

  * HTML5 Video Attributes
    * autoplay - please don't use this for videos :)
    * controls
    * muted
    * loop
  * Tracks & subtitles
  * Media Fragments (currently only 't' or the time)

## Quick Usage With Defaults

Placing this twig tag in your template:

```twig
{{ html5video(record.video) }}
```
Will use the defaults found in the extensions config file

```yaml
default:
  use_cdn: false
  save_data: false
  attributes: [ 'controls']
  preload: 'metadata'
  multiple_source: true
  video_types: [ 'webm', 'mp4' ]
```

and produce a video tag in your template.

```html
<video controls preload="metadata">
  <source src="/files/your-video.webm" type="video/webm" >
  <source src="/files/your-video.mp4" type="video/mp4" >
</video>
```

## Usage Walkthrough

To start off you'll need to have a field in your contenttypes that accepts/uses video. While building this extension I used the ``file`` type.

 ```yaml
 # your contenttypes.yml file
 entries:
     name: Entries
     singular_name: Entry
     fields:
         # other fields here
         video: # our video field to call in the template
             type: file # the type of field we are using.
```

Then create a config section in the extensions config file if you don't want to use the defaults.

```yaml
blogVideos:
  use_cdn: false
  save_data: false
  video_poster: 'path/to/poster.png' # if you want a poster image to load
  attributes: [ 'controls']
  preload: 'metadata'
  width_height: [ 400, 400 ] # the width and height of the video element if you have one... optional
  multiple_source: true
  video_types: [ 'webm', 'mp4' ]
  tracks:
    en_subtitles:
      kind: 'subtitles'
      srclang: 'en'
      label: 'English subtitles'
      src: '/theme/base-2016/file.vtt'
      default: true
    es_subtitles:
      kind: 'subtitles'
      srclang: 'es'
      label: 'Español subtitles'
      src: '/theme/base-2016/file-es.vtt'
    en_captions:
      kind: 'captions'
      srclang: 'en'
      label: 'English Captions'
      src: '/theme/base-2016/captions.vtt'
```


Has options for ```tracks``` and WebVTT files for subtitles http://html5doctor.com/video-subtitling-and-webvtt/#contents