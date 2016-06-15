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

Then create a config section in the extensions config file if you don't want to use the defaults. Give this a name. Settings left out of your named config will fall back to whatever is set in the 'default' config section.

```yaml
blogVideos:
  use_cdn: false
  save_data: false
  video_poster: 'path/to/poster.png' # if you want a poster image to load
  attributes: [ 'controls', 'muted' ]
  preload: 'metadata'
  width_height: [ 400, 400 ] # the width and height of the video element if you have one... optional
  multiple_source: true
  video_types: [ 'webm', 'mp4' ]
```

In the template file you are using a video use this extensions twig tag along with your named config like so:

```twig
{{ html5video(record.video, 'blogVideos' ) }}
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
{{ html5video(record.video, 'blogVideos', { preload: [ 'auto' ] } )  }}
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


Has options for ```tracks``` and WebVTT files for subtitles http://html5doctor.com/video-subtitling-and-webvtt/#contents