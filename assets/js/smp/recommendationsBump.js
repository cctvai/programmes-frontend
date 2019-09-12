define(function() {
    function RecommendationsBump () {
        this.init();
    }

    RecommendationsBump.prototype.init = function() {
        this.validStatsFields = [
            "link_location",
            "prev_content_name",
            "prev_content_count",
            "prev_rec_feed",
            "prev_rec_source",
            "prev_rec_alg",
            "prev_rec_position",
            "prev_content_position",
            "clip_id",
            "episode_id"
        ];
    };

    return RecommendationsBump;
});
