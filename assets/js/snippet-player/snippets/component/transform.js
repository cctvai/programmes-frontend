define([
    '../util/dom',
    '../util/obj',
    'jquery-1.9'
], function(dom, obj, $) {

    /**
     * NB! legacy-breaking change:
     * the following code works with <bbc-snippet data-record-id="123" />
     * no longer supported: <bbc-snippet><record-id>123</record-id></bbc-snippet>
     */


    /**
     * get the snippet ID from e.g. a <bbc-snippet> element
     * @param {HTMLElement} el
     * @returns {?string}
     */
    function getRecordIdFromSnippet(el) {
        return ('recordId' in el.dataset)
            ? el.dataset['recordId']
            : console.error('bbc-snippet but no data-record-id.');
    }

    /**
     * find the snippet element corresponding to this ID
     * @param {string} id
     * @returns {?HTMLElement}
     */
    function getSnippetFromRecordId(id) {
        return document.querySelector('bbc-snippet[data-record-id="'+id+'"]');
    }

    return {

        /**
         * get an array of IDs of <bbc-snippet> elements found on page and process them
         */
        process: function() {
            var recordIds = Array.prototype.slice.call(document.querySelectorAll('bbc-snippet'))
                .map(getRecordIdFromSnippet)
                .filter(Boolean);
            if (!recordIds.length) return;
            this.request(recordIds, this.replace);
        },

        /**
         * fetch the HTML for these snippets from server
         * @param {string[]} ids
         * @param {function} onSuccess callback
         * @param {function?} onError callback
         */
        request: function(ids, onSuccess, onError) {
            var url = '/programmes/snippet/' + encodeURIComponent(ids.join(',')) + '.json';
            $.ajax({
                url: url,
                dataType: 'json',
                success: onSuccess,
                error: onError || function(request, status, error) {
                    console.error('Error when calling snippet URL: ' + url, request, status, error);
                },
            });
        },

        /**
         * replace <bbc-snippet> placeholders with rendered HTML
         * @param {array} snippets
         */
        replace: function(snippets) {
            if (!snippets) return;
            snippets.forEach(function (snippet) {
                var snip = getSnippetFromRecordId(snippet.id);
                if (!snip) return console.error('id ' + id + ', but snippet not found?');

                var el = document.createElement('div');
                el.innerHTML = snippet.html;
                snip.parentElement.replaceChild(el, snip);
            });
        }
    };

});
