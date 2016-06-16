# Bolt HTML5 Video Extension

A Bolt Extension that allows you to play local videos either through ```/files/``` or from your own CDN.

Quick Navigation:

* [ Quick Usage With Defaults ](#quick-usage-with-defaults)
* [Usage Walk-through](#usage-walk-through)
* [Using Your Own CDN](#using-your-own-cdn)
* [Adding Tracks and Subtitles](#adding-tracks-and-subtitles)
* [Controlling Number of Video Sources](#controlling-video-sources)
* [Advanced Usage - over ride settings](#advanced-usage)

Has current support for:

  * HTML5 Video Attributes
    * autoplay - please don't use this for videos :)
    * controls
    * muted
    * loop
  * preload options - metadata | auto | none
  * video posters
  * video width and height
  * Tracks & subtitles
  * Media Fragments (currently only 't' or the time)
  * single or multiple video sources


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

## Usage Walk-through

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

If you're not using the default settings then create a new settings group in the extensions config file.

```yaml
# extensions config file
blogVideos: # the name of our settings group!
```

Then follow the same structure in the *default* settings. Any setting left out of your new settings group will fall back to whatever is set in ``default``.


```yaml
blogVideos:
  use_cdn: false
  save_data: false
  video_poster: 'path/to/poster.png'
  attributes: [ 'controls', 'muted' ]
  preload: 'metadata'
  width_height: [ 400, 400 ]
  multiple_source: true
  video_types: [ 'webm', 'mp4' ]
```

Now in your template ( example: record.twig ) place this tag with your named settings group wherever you want a video!

```twig
{{ html5video(record.video, 'blogVideos' ) }}
```

## Using Your Own CDN

There is two (2) ways to use a CDN. The first is to add your CDN URL to the extensions config setting of ``cdn_url``

```yaml
cdn_url: https://your-cdn.com/path/to/files/
```

Then in your templates where you want the video use the file name:

```twig
{{ html5video( 'your-file.webm' ) }}
```

This will produce in the rendered HTML

```html
<video controls preload="metadata">
  <source src="https://your-cdn.com/path/to/files/your-file.webm" type="video/webm" >
  <source src="https://your-cdn.com/path/to/files/your-file.mp4" type="video/mp4" >
</video>
```

The second way to do this is leave the ``cdn_url`` setting empty and place the full URL to your video in the tag:

```twig
{{ html5video( 'https://your-cdn.com/path/to/videos/second-cdn-example.webm' ) }}
```

This will produce in the rendered HTML

```html
<video controls preload="metadata">
  <source src="https://your-cdn.com/path/to/videos/second-cdn-example.webm" type="video/webm" >
  <source src="https://your-cdn.com/path/to/videos/second-cdn-example.mp4" type="video/mp4" >
</video>
```

## Adding Tracks and Subtitles

You add subtitles and tracks to your videos by adding a section to your named config titled *tracks*


```yaml
blogVideos:
  # other config settings here
  tracks:
```

It is recommended to prefix each subsection with the language of the particular subtitle or captions. Each of these subsections will need to have

* kind: what kind of track are you providing? Values to place here are 'subtitles' or 'captions'
* srclang: What is the source language of the provided file.
* label: For English language subtitles or captions give it a descriptive label like - 'English Subtitles'
* src: the path to the file. if the provided file is in your theme directory give that path, ie: '/theme/base-2016/your-video-subtitles.vtt'
* default: if this is the default file mark this as true. Otherwise leave it out of the section.

Here is a completed example of multiple language subtitles and English Captions:

```yaml
blogVideos:
  # other settings here
  tracks:
   en_subtitles:
     kind: 'subtitles'
     srclang: 'en'
     label: 'English subtitles'
     src: '/theme/base-2016/your-video-subtitles.vtt'
     default: true
   es_subtitles:
      kind: 'subtitles'
      srclang: 'es'
      label: 'Español subtitles'
      src: '/theme/base-2016/your-video-subtitles-es.vtt'
   en_captions:
      kind: 'captions'
      srclang: 'en'
      label: 'English Captions'
      src: '/theme/base-2016/captions.vtt'
```

This will give you in your rendered page:

```html
<video controls preload="metadata" poster="/path/to/poser.png" width="400" height="400">
  <source src="/files/your-video.webm" type="video/webm" >
  <source src="/files/your-video.mp4" type="video/mp4" >
  <track label="English subtitles" kind="subtitles" srclang="en" src="/theme/base-2016/your-video-subtitles.vtt"  default >
  <track label="Español subtitles" kind="subtitles" srclang="es" src="/theme/base-2016/your-video-subtitles-es.vtt" >
  <track label="English Captions" kind="captions" srclang="en" src="/theme/base-2016/captions.vtt" >
</video>
```

For a deeper dive into WebVTT have a look at the following links:

* [HTML5 Doctor: Video Subtitling and WebVTT](http://html5doctor.com/video-subtitling-and-webvtt/)
* [Mozilla Developer Network: Introduction to WebVTT](https://developer.mozilla.org/en-US/docs/Web/API/Web_Video_Text_Tracks_Format)


## Controlling Video Sources

When the config has ``multiple_source`` set to *true* as in the default settings this extension will use the file types specified in ``video_types``. Typically these are [MP4 - caniuse.com](http://caniuse.com/#search=mp4) and [WEBM - caniuse.com](http://caniuse.com/#search=webm) types. This will serve two (2) files.

```yaml
# The config file
default:
  use_cdn: false
  save_data: false
  attributes: [ 'controls']
  preload: 'metadata'
  multiple_source: true
  video_types: [ 'webm', 'mp4' ]
```

```html
<video controls preload="metadata">
  <source src="/files/your-video.webm" type="video/webm" >
  <source src="/files/your-video.mp4" type="video/mp4" >
</video>
```

If you would prefer to only serve one (1) file set ``multiple_source`` to false and then pass either a CDN url or the video attached to the record you want. The file that will be served is whatever you pass in the template or the one you've uploaded to files or your record.

One File with a record's video:

```twig
{# Your Twig Template ie. 'record.twig` #}
{{ html5video(record.video ) }}
```
```html
<!-- The Rendered HTML in your page -->
<video controls preload="metadata" src="/files/your-video.mp4"></video>
```

One file with your own CDN:

```twig
{# Your Twig Template ie. 'record.twig` #}
{{ html5video('https://your-cdn.com/videos/your-video.webm') }}
```
```html
<!-- The Rendered HTML in your page -->
<video controls preload="metadata" src="https://your-cdn.com/videos/your-video.webm"></video>
```


## Advanced Usage
You can over-ride default config settings on a per usage basis in your templates.

Settings that can be currently over-ridden are:

* video_poster
* use_cdn
* preload
* width_height
* media_fragment
* multiple_source

To over-ride these in a template place the tag in the template along with the named config:

```twig
{{ html5video(record.video, 'blogVideos' ) }}
```

After your named config place a comma then your custom config setting(s).

```twig
{{ html5video(record.video, 'blogVideos', { preload: 'auto' } )  }}
```

For multiple over-rides I would suggest placing them on a new line like so:

```twig
{{ html5video(record.video, 'blogVideos', {
  video_poster: 'path/to/custom/poster.png',
  use_cdn: true,
  preload: 'auto'
  width_height: [ 600, 400 ],
  media_fragment: [ 0, 60 ],
  multiple_source: false
  } )
}}
```


## Config Settings:

__cdn_url__:
set CDN url if its set and the config option of "use_cdn" is true then use this path for the video.
example:

```yaml
cdn_url: 'https://awesome-cdn.com/path/to/videos/'
```

__use_cdn__:
If you want to use a CDN. Defaults to false - or no you don't want to.

```yaml
use_cdn:  [true | false ]
```

If the above two are set then you can use just the filename in your templates to get the full path to the video. If there is no ``cdn_url`` set and ``use_cdn`` is true then you need to put the full url in your template.
EX:

```twig
{{ html5video( 'https://your-cdn.com/path/to/videos/example.webm' ) }}
```
