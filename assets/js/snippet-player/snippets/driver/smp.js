define(['../playables'], function(playables) {

    function normalizeOpts(opts) {
        var nopts = Object.assign( {
            statsObject: {},
        }, opts);

        nopts.statsObject = Object.assign({
            producer: '',
            destination: '',
            playlistName: '',
            sessionLabels: {}
        }, nopts.statsObject);

        nopts.statsObject.sessionLabels = Object.assign({
            // list of managed labels
            // https://github.com/bbc/echo-client-js/blob/master/src/echo/enumerations.js#L248
            app_version: 'SLappVersion',
            bbc_site: 'SLbbcSite',
            playlist_type: 'SLplaylistType'
        }, nopts.statsObject.sessionLabels);

        return nopts;
    }

    var container,
        audio,
        current,
        inited = false,
        paused = false;

    var mappings = {
        'ended': 'playlistEnded'
    };

    var mapEventName = function(eventName) {
        if (mappings[eventName])
            return mappings[eventName];
        return eventName;
    };

    var transformPlayable = function(playable) {
        if (playable instanceof playables.ClipAudioSource)
            return {
                kind: 'radioProgramme',
                vpid: playable.getId()
            };
        else
            return {
                kind: 'radioProgramme',
                href: playable.getSrc()
            };
    };

    var driver = {

        init: function(bump, opts) {
            if (inited)
                return console.warn('driver/smp.js already inited');

            container = document.createElement('div');
            container.setAttribute('class', 'spt-smp');
            document.body.appendChild(container);

            var normalOpts = normalizeOpts(opts);
            var playerOpts = Object.assign({

                ui: {
                    enabled: false, // hide
                    hideDefaultErrors: true, // Prevents IE install Flash prompt
                },

                // The SMP player will fill the container rather than
                // requiring width and height to be set
                responsive: true,

                // Tell the SMP that it should attempt playback in page
                // rather than through a separate player application
                preferInlineAudioPlayback: true,

                // Workaround to prevent loading the BBC media player
                // on Android prior to Kitkat
                preferHtmlOnMobile: true,

                playlistObject: {
                    items: [{
                        kind: 'radioProgramme',
                        href: 'http://emp.bbci.co.uk/emp/media/blank.mp3'
                    }]
                },

                autoplay: false,

                // Additional data for DAx reporting
                appName: 'snippets-smp-driver',
                appType: 'responsive',
                counterName: '?',
                statsObject: {} // normalOpts overwrites this

            }, normalOpts);

            audio = bump(container).player(playerOpts);

            audio.bind('error', function(err) {
                var kpis = {
                    'critical': 'SMP_Critical',
                    'error': 'SMP_Error',
                    'warning': 'SMP_Warning'
                };
            });

            audio.load();
            inited = true;
        },

        destroy: function() {
            if (!inited)
                return console.error('driver/smp destroy() but not inited');

            document.body.removeChild(container);
            container = null;
            audio.unbind('error');
            audio = null;
            current = null;

            // Indicate that the driver has been destroyed and will need to
            // be reinitialised before use
            inited = false;
        },

        play: function(playable, startTime, playlistOpts, opts) {
            playlistOpts = playlistOpts || {};
            opts = opts || {};
            // Store a reference to the playable item currently being played,
            // this is used to indicate that the driver is in the correct state
            // to apply playback functions
            current = playable;

            var playlist = Object.assign({
                items: [transformPlayable(playable)],
            }, playlistOpts);

            var smpOpts = Object.assign({
                autoplay: true,
                startTime: startTime || 0,
            }, opts);

            audio.loadPlaylist(playlist, smpOpts);
            paused = false;

            return this;
        },

        pause: function() {

            // Only attempt a pause on the SMP
            if (current) {
                audio.pause();
                paused = true;
            }
            return this;
        },

        resume: function() {
            if (current && paused) {
                audio.play();
                paused = false;
            }
            return this;
        },

        stop: function() {

            // Must check if paused otherwise SMP will throw an error:
            // Uncaught Error: Error: An invalid exception was thrown.
            if (current && !paused) {
                audio.suspend();
                current = null;
            }
            return this;
        },

        setVolume: function(n) {
            audio.volume(n);
            return this;
        },

        getDuration: function() {
            return audio.duration();
        },

        getCurrentTime: function() {
            return audio.currentTime();
        },

        setCurrentTime: function(time) {
            audio.currentTime(time);
            return this;
        },

        on: function(eventName, listener) {
            audio.bind(mapEventName(eventName), listener);
            return this;
        },

        off: function(eventName, listener) {
            audio.unbind(mapEventName(eventName), listener);
            return this;
        }

    };

    return driver;
});
