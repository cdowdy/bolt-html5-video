# NO LONGER MAINTAINED. NO FIXES OR UPDATES WILL BE DONE FOR THE CURRENT SUPPORTED VERSION BOLT OR ANY FUTURE VERSIONS OF BOLT CMS


# Bolt HTML5 Video Extension

A Bolt Extension that allows you to play local videos either through ```/files/``` or from your own CDN. Also lets you say goodbye to heavy GIFs and allows you to use new video codec sweetness for short animations in Bolt!  
 
 For a few usage examples with videos and a gif like animation you can see [Bolt HTML5 Videos Demo](https://corydowdy.com/demo/bolt-html5-videos) on my site. 

Quick Navigation:

* [Extension Set Up](#set-up)
* [ Quick Usage With Defaults ](#quick-usage-with-defaults)
* [Using Your Own CDN](#using-your-own-cdn)
* [Adding Video Attributes](#video-attributes)
* [Controlling Number of Video Sources](#controlling-video-sources)
* [Adding Tracks and Subtitles](#adding-tracks-and-subtitles)
* [Controlling Start and End Times](#controlling-start-and-end-times)
* [Advanced Usage - over ride settings](#advanced-usage)
* [Uploading Video Files](#uploading-video-files)
* [Using Both ``file`` & ``filelist`` types](#using-both-file-and-filelist-types)

Has current support for:

  * HTML5 Video Attributes
    * autoplay - please don't use this for videos :)
    * controls
    * muted
    * loop
    * playsinline - this is used particularly for ios 10 
  * preload options - metadata | auto | none
  * video posters
  * video width and height
  * custom classes and id's
  * Tracks & subtitles
  * Media Fragments (currently only 't' or the time)
  * single or multiple video sources
  
  
## NOTICE:  
a recent change to Bolt may strip certain tags from the rendered output. You'll need to add these to your Bolt Config. [Here is the relevant section in the config](https://github.com/bolt/bolt/blob/868e36f2961a98745131c1f0b2b13f711deb6345/app/config/config.yml.dist#L221-L223)  

```html 
htmlcleaner:
  allowed_tags: [video, source, track, ... other tags ]
```   

You'll also need to allow these attributes so the too will not be removed:    

```html 
htmlcleaner:
  allowed_tags: [video, source, track, ... other tags ]  
  allowed_attributes: [ preload, controls, muted, autoplay, playsinline, loop, poster, type, label, kind, srclang, .. other attributes here  ]
``` 

# Upgrade Guide  
Using v2.x+ of this extension you'll no longer need to access the index of any `filelist` type. That is if you have in your twig template a piece of code that looks like as follows:  

```twig 
{# old code that will no longer work #}
{{ html5video(record.video.0, 'blogVideos' ) }}  
```  

You can now remove the `.0` part of the twig call for your file. It will now look as follows:  

```twig 
{{ html5video(record.video, 'blogVideos' ) }}  
```    
If you don't remove the `.0` then well... the newest version of this extension wont work :) and you should if you can move to use the custom field type of ``h5video``  

These settings are no longer used and if they are in your templates for over-rides you **MUST** remove them. There is no graceful fallback for these and if they are present the extension will "fail" to output a video and cause your site to display an error.

```yaml
# Removed Config Options No Longer Used:
save_data:
multiple_source:
video_types:
```

*as of Bolt 3.2.14(+?) custom fields will give you a flashbag warning*  
> In the ContentType for 'Entry', the field 'yourFieldName' has 'type: h5video', which is not a proper field type. Please edit contenttypes.yml, and correct this.  

*everything "works" and this should hopefully be fixed eventually. Relevant Bolt issue: https://github.com/bolt/bolt/issues/6782*



## Set Up  

As of Bolt HTML5 Video v2.x+ there is a custom field you can use in your contentType's. Using this custom field you can upload files and also have a preview after saving. To use this custom field enter in the type `h5video` in your contentType.  

 ```yaml
# your contenttypes.yml file
entries:
  name: Entries
  singular_name: Entry
  fields:
    # other fields here
    video: # our video field to call in the template
        type: h5video # the type of field we are using.
```  

 You can now move on to the other sections of this readme and ignore the following section(s) dealing with `file` and `filelist` field types.
<!--
To start off you'll need to have a field in your contenttypes that accepts/uses video. There are two types right off the bat you can use. The``file`` type and/or ``filelist`` type.

**If You Plan To Upload Videos through the 'Edit' Option of the Backend With 'Upload File' You'll Need To Use the ``filelist`` Type**. This is because Bolt's backend will place the video in a directory. This extension assumes each file will be named the same. So an MP4 file will have the same name and file path as a Webm or OGG.

Regular File upload through 'upload files'

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

Uploading Files through the record's edit screen:

 ```yaml
# your contenttypes.yml file
entries:
  name: Entries
  singular_name: Entry
  fields:
    # other fields here
    video:
        type: filelist
```
-->

### Configuration   

If you're not using the default settings then create a new settings group in the extensions config file.

```yaml
# extensions config file
blogVideos: # the name of our settings group!
```

Then follow the same structure in the *default* settings. Any setting left out of your new settings group will fall back to whatever is set in ``default``.


```yaml
blogVideos:
  use_cdn: false
  video_poster: 'path/to/poster.png'
  attributes: [ 'controls', 'muted' ]
  preload: 'metadata'
  width_height: [ 400, 400 ]
```

Now in your template ( example: record.twig ) place this tag with your named settings group wherever you want a video!

```twig
{{ html5video(record.video, 'blogVideos' ) }}
```  

1) ``record`` is the current page being edited/created.
2) ``video`` is the field we are using to upload/pick our videos - h5video above in our contentType setup
3) ``blogVideos`` is the custom config group we created above


## Quick Usage With Defaults

Placing this twig tag in your template:

```twig
{{ html5video(record.video, 'default' ) }}
```
Will use the defaults found in the extensions config file

```yaml
default:
  use_cdn: false
  attributes: [ 'controls']
  preload: 'metadata'
```

and produce a video tag in your template.

```html
<video controls preload="metadata">
  <source src="/files/your-video.webm" type="video/webm" >
  <source src="/files/your-video.mp4" type="video/mp4" >
</video>
```

## Using Your Own CDN

There are two (2) ways to use a CDN. The first is to add your CDN URL to the extensions config setting of ``cdn_url``

```yaml
cdn_url: https://your-cdn.com/path/to/files/
```

Then in your templates where you want the video use the file name:

```twig
{{ html5video( 'your-file.webm', 'yourConfigName' ) }}
```

This will produce in the rendered HTML

```html
<video controls preload="metadata">
  <source src="https://your-cdn.com/path/to/files/your-file.webm" type="video/webm" >
</video>
```

The second way to do this is leave the ``cdn_url`` setting empty and place the __full URL__ to your video in the tag:

```twig
{{ html5video( 'https://your-cdn.com/path/to/videos/second-cdn-example.webm', 'yourConfigName' ) }}
```

This will produce in the rendered HTML

```html
<video controls preload="metadata">
  <source src="https://your-cdn.com/path/to/videos/second-cdn-example.webm" type="video/webm" >
</video>
```  

## Video Attributes {#video-attributes}
Video attributes are boolean. If you want them you add them to the config array. Options are:  

* autoplay - immediately play the video as soon as it can  
* controls - give the user playback controls ie: play, pause, seek 
* muted    
* loop - continuously loop the video. Much like gif's. 

And since iOS is a snowflake and all snowflakes are unique :) you can also add:  

* playsinline  

which is telling iOS Safari 10+ that you want to play the video "inline" in the document by default, within the dimensions set by the video element, instead of being displayed fullscreen or in an independent resizable window.  

example with all Attributes:  

```yaml
blogVideos:
  attributes: [ 'autoplay', 'controls', 'muted', 'loop', 'playsinline'  ]
```  

HTML output:  

```html
<video autoplay controls muted loop playsinline>
  <source src="/files/your=file.webm" type="video/webm" >
  <source src="/files/your=file.mp4" type="video/mp4" >
</video>
```  

## Adding Width's and Heights  
When adding width and heights the format is as follows:  

```yaml
blogVideos:
  width_height: [ your-width, your-height ]
```  

If you want to use a percentage: ie 100%, you **MUST** put it in quotes:  

```yaml
blogVideos:
  width_height: [ 400, '100%' ]
```  

This will produce in the rendered HTML

```html
<video width="400" height="100%">
  <source src="/files/your=file.webm" type="video/webm" >
  <source src="/files/your=file.mp4" type="video/mp4" >
</video>
```  



## Controlling Video Sources

There are at least two ways to control how many files will be used for the video tags sources. The first one is to upload/pick them them in your contentType through the backend. This works for both the custom field of `h5video` and for the `filelist` type.  

The second way to do this is to insert those in your twig templates in an array.   

```twig
{# Your Twig Template ie. 'record.twig` #}
{{ html5video(['your-video.mp4', 'your-video.webm'], 'default' ) }}
```  
See [Bolt HTML5 Videos Demo](https://corydowdy.com/demo/bolt-html5-videos) for another example on using multiple videos in a twig template.

This will produce a video tag similar to this:  

```html
<video controls preload="metadata">
  <source src="/files/your-video.webm" type="video/webm" >
  <source src="/files/your-video.mp4" type="video/mp4" >
</video>
```

Multiple Files with your own CDN:

Either have your cdn URL set in the config under `cdn_url`

```yaml 
# app/config/extensions/html5video.cdowdy.yml file
cdn_url: 'https://your-cdn.com/videos/` 
```  
and then put the filename of your video(s) in your template.  

```twig
{# Your Twig Template ie. 'record.twig` #}
{{ html5video( ['your-video.webm', 'your-video.mp4'], 'default' ) }}
```  

If you don't set your CDN URL you can supply the **FULL URL** to your videos in your template.
  
```twig 
{# Your Twig Template ie. 'record.twig` #} 
{# a single video from your CDN #}  
{{ html5video('https://your-cdn.com/videos/your-video.webm', 'default' ) }}

{# multiple videos from your CDN #}
{{ html5video(['https://your-cdn.com/videos/your-video.webm', 'https://your-cdn.com/videos/your-video.mp4'], 'default' ) }}
```
```html
<!-- The Rendered HTML in your page for multiple Videos -->
<video controls preload="metadata">
  <source src="https://your-cdn.com/videos/your-video.webm" type="video/webm" >
  <source src="https://your-cdn.com/videos/your-video.webm" type="video/mp4" >
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

Also see the Note on [VTT Mime Type Console Warnings](#gotchas-and-fyis)

## Controlling Start and End Times

Through the ``media_fragment: [ ]`` config setting you can start the video or end the video at a specific time.

The example below starts the video at five seconds and will stop playing at twelve seconds.

```yaml
blogVideos:
  #other settings here
  media_fragment: [ 5, 12 ]
```  
 
```html
<video controls preload="metadata">
  <source src="/files/your-video.webm#t=5,12" type="video/webm" >
  <source src="/files/your-video.mp4#t=5,12" type="video/mp4" >  
</video>
```

For more examples on this see [Mozilla Developer Network - HTML5 Video Specifying Playback Range ](https://developer.mozilla.org/en-US/docs/Web/Guide/HTML/Using_HTML5_audio_and_video#Specifying_playback_range)

## Advanced Usage
You can over-ride default config settings on a per usage basis in your templates.

Settings that can be currently over-ridden are:

* video_poster
* use_cdn
* class
* preload
* width_height
* media_fragment
* multiple_source

To over-ride these in a template place the tag in the template along with the named config:

```twig
{{ html5video( record.video, 'blogVideos' ) }}
```

After your named config place a comma then your custom config setting(s).

```twig
{{ html5video( record.video, 'blogVideos', { preload: 'auto' } )  }}
```

or to add an ID for a JavaScript hook:

```twig
{{ html5video( record.video, 'blogVideos', { video_id: 'js-id' } ) }}
```


For multiple over-rides I would suggest placing them on a new line like so:

```twig
{{ html5video( record.video, 'blogVideos', {
  video_poster: 'path/to/custom/poster.png',
  use_cdn: true,
  preload: 'auto',
  width_height: [ 600, 400 ],
  media_fragment: [ 0, 60 ],
  multiple_source: false
  } )
}}
```  

## Uploading Video Files

**Uploading through Bolt's Admin backend**:  
**Only relevant if you're using the `file` or `filelist` field types.**

1). Under the ``Settings`` navigation area click ``File Management > Uploaded Files ``

2). Either create a folder for your videos or choose ``select file``
  * When uploading choose all the video types you would like to serve. That is webm, mp4 or ogg.

3). Click upload file.

Uploading your files this way will allow you to use ``record.video`` portion in your twig tag.

```twig
{{ html5video( record.video) }}
```

**Uploading through a record / contenttypes creation or edit page**:

1). You'll need to use ``filelist`` in your contenttypes video field.

 ```yaml
# your contenttypes.yml file
entries:
  name: Entries
  singular_name: Entry
  fields:
    # other fields here
    videolist:
        type: filelist
```

2). When Editing your contenttype choose the field you're using for videos ( this example above is 'videolist' ) and select 'upload file'

3). Chose all the filetypes you wish to serve for that particular record, ie webm, mp4 or ogg and upload those video files.

4). In your template you can now use:

```twig
{{ html5video( record.videolist ) }}
```

## Using Both file and filelist types:

Structure your contenttype to use both ``file`` and ``filelist``

```yaml
# your contenttypes.yml file
entries:
  name: Entries
  singular_name: Entry
  fields:
    video:
      type: file
    videolist:
      type: filelist
```


```twig
{% if record.videolist %}
  {{ html5video( record.videolist, 'blogVideos') }}
{% endif %}
{% if record.video %}
  {{ html5video( record.video, 'blogVideos' ) }}
{% endif %}

{# of you could use one like this #}
{% if record.videolist %}
  {{ html5video( record.videolist, 'blogVideos') }}
{% else %}
  {{ html5video( record.video, 'blogVideos' ) }}
{% endif %}
```


## Gotchas and FYI'S

1. If your using version 1.x and uploading through the contenttypes record edit/creation page you should use the filelist type instead of the file type  

2. If you get a Console Error of ``Resource interpreted as TextTrack but transferred with MIME type text/plain:`` when including text tracks (vtt files) you need to add the correct mime type for your server:  
  * Apache:  
   ```  
   <IfModule mod_mime.c>
     AddType text/vtt    vtt
   </IfModule>  
   ``` 
   * NGINX:  
   ```  
   types {
   #others here
       text/vtt                              vtt;
   }  
   ``` 
3. The Custom Field Type of `h5video` currently throws a warning. As of right now you can dismiss and ignore that. This is a possible bug in bolt. See: https://github.com/bolt/bolt/issues/6782  
4. If you have set a CDN URL and also have set your named config to use a cdn (``use_cdn: true``) and supply the full URL in your twig templates to the CDN hosted file, you'll get the wrong/malformed URL. Example below:  

```yaml  
# extensions config

cdn_url: 'https://mycdn.com/` 
  
usesCDN:
  use_cdn: true
  attributes: [ 'controls']
  preload: 'metadata' 

```  
```twig  
{# your twig template. ie: record.twig #}
{{ html5video( 'https://mycdn.com/my-file.mp4', 'usesCDN') }}
```  
HTML that is produced:  
```html
<video controls preload="metadata">
  <source src="https://mycdn.com/https://mycdn.com/my-file.mp4" type="video/mp4" >  
</video>
```  

you'll need to either remove the ``cdn_url:`` or the full URL from the twig template.
