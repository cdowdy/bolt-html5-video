/**
 * Created by Cory on 7/11/2016.
 */
(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        define([], factory(root));
    } else if (typeof exports === 'object') {
        module.exports = factory(root);
    } else {
        root.saveData = factory(root);
    }
})(typeof global !== 'undefined' ? global : this.window || this.global, function (root) {

    'use strict';

    var saveData = {};
    var settings;
    var vidButtons = document.querySelectorAll('.loadVideo');
    var docFrag = document.createDocumentFragment();


    /**
     * loop through the data-attributes and add the class using classList
     * @param config
     * @param classes
     * @param element
     */
    var addClass = function (config, classes, element) {
        // set the classes if they exist
        if (config) {
            for (var i = 0; i < classes.length; i++) {
                element.classList.add(classes[i]);
            }
        }
    };


    /**
     * set the Video boolean atttributes - ie: controls, muted, loop or autoplay
     * @param config
     * @param element
     * @param attribType
     * @param attribute
     */
    var setVideoAttributes = function (config, element, attribType, attribute) {

        if (config) {
            element.setAttribute(attribType, attribute);
        }
    };


    /**
     *
     * @param selector
     */
    var createVideoSources = function (selector) {
        var vidData = selector.getAttribute('data-save');
        var videos = JSON.parse(vidData), key;

        for (key in videos) {

            if (videos.hasOwnProperty(key)) {
                var source = document.createElement('source');
                source.src = key;
                source.type = 'video/' + videos[key];
                docFrag.appendChild(source);
            }

        }
    };



    /**
     * create and add video tracks to the video element
     * @param selector
     * @param appendElement
     */
    var createVidTracks = function (selector, appendElement) {
        var trackData = selector.getAttribute('data-tracks');
        var parsedTrack = JSON.parse(trackData);
        var keys = Object.keys(parsedTrack), key;


        keys.forEach(function (prop) {
            var track = document.createElement('track');
            for (var k in  parsedTrack[prop]) {
                if (parsedTrack[prop].hasOwnProperty(k)) {
                    track.kind = parsedTrack[prop].kind;
                    track.srclang = parsedTrack[prop].srclang;
                    track.label = parsedTrack[prop].label;
                    track.src = parsedTrack[prop].src;
                    if (parsedTrack[prop].default) {
                        track.default = 'default';
                    }
                }
            }

            docFrag.appendChild(track);
        });

    };

    /**
     *
     * @param event
     * @param options
     */
    var createVid = function (event, options) {

        var button = event.target;
        var buttonParent = button.parentNode;

        // create the video element
        var videoElement = document.createElement('video');

        // Grab All Our Data-Attributes with our data
        var videoID = button.getAttribute('data-id'),
            videoClass = button.getAttribute('data-class'),
            attribs = button.getAttribute('data-attribs'),
            preload = button.getAttribute('data-preload'),
            widthHeight = button.getAttribute('data-width-height'),
            poster = button.getAttribute('data-poster'),
            tracks = button.getAttribute('data-tracks');

        var classJSON = JSON.parse(videoClass),
            attribJSON = JSON.parse(attribs),
            whJSON = JSON.parse(widthHeight), key;


        // set the video ID if it exits
        setVideoAttributes(videoID, videoElement, 'id', videoID);

        // set the preload attribute if it exists
        setVideoAttributes(preload, videoElement, 'preload', preload);

        // set the poster if it exists
        setVideoAttributes(poster, videoElement, 'poster', poster);

        // set the videos boolean attributes - ie: controls, muted, loop, autoplay
        for (var a = 0; a < attribJSON.length; a++) {
            setVideoAttributes(attribs, videoElement, attribJSON[a], '');
        }

        // set the classes if they exist
        addClass(videoClass, classJSON, videoElement);


        // if (widthHeight) {
        //     for (key in whJSON) {
        //         if (whJSON.hasOwnProperty(key)) {
        //             videoElement.setAttribute('width', whJSON[key]);
        //         }
        //     }
        // }
        setVideoAttributes(whJSON[0], videoElement, 'width', whJSON[0]);
        setVideoAttributes(whJSON[1], videoElement, 'height', whJSON[1]);

        // add and append the video sources to the video element
        createVideoSources(button);

        // append Video Tracks after sources
        if (tracks) {
            createVidTracks(button, videoElement);
        }

        videoElement.appendChild(docFrag);
        // insert the video
        buttonParent.appendChild(videoElement);

        // button.classList.add('button-clicked');
        button.setAttribute('hidden', '');
        button.setAttribute('style', 'display:none');
        button.previousElementSibling.setAttribute('hidden', '');

    };


    /**
     * pass all events and methods here to be handled in the init method
     *
     * @param event
     */
    var eventHandler = function (event) {
        // on the button click defined in 'buttonClass'
        // create and insert the video.
        createVid(event);

    };


    /**
     * destroy all settings of this b
     */
    saveData.destroy = function () {
        if (!settings) {
            return;
        }

        // Remove event listeners
        root.document.removeEventListener('click', eventHandler, false);

        settings = null;
    };

    /**
     * script entry point
     * @param options
     */
    saveData.init = function (options) {

        // destroy the current initialization of a plugin
        saveData.destroy();

        // listen for the click event handler
        for (var i = 0, len = vidButtons.length; i < len; i++) {
            vidButtons[i].addEventListener('click', eventHandler, false);
        }
    };

    return saveData;

});

// lets run this mother
saveData.init();
