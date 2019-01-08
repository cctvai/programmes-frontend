<?php

namespace App\DsShared\Helpers;

/**
 * Class FixIsiteMarkupHelper
 *
 * Please note that nothing in this class is really intended to make sense. It exactly replicates
 * the HTML handling in V2, which is what editors will have shaped all existing content in iSite to,
 * in order to make their pages work.
 */
class FixIsiteMarkupHelper
{
    /**
     * Cleanup the content and split into paragraphs (Same as v2)
     *
     * @param string $rawMarkup
     * @return string[]
     */
    public function getParagraphs(string $rawMarkup): array
    {
        $paragraphs = [];
        if (empty($rawMarkup)) {
            return $paragraphs;
        }
        $rawParagraphs = explode('</p>', $rawMarkup);
        foreach ($rawParagraphs as $paragraph) {
            $paragraph = $this->cleanupText($paragraph);
            if (!empty($paragraph) && strlen($paragraph) > 5) { // paragraphs shorter than 5 characters are spacing characters
                $paragraph = $this->fixMarkup($paragraph);
                $paragraphs[] = $paragraph;
            }
        }
        return $paragraphs;
    }

    /**
     * Fix the markup so it's valid and semantic (Same as v2)
     *
     * @param string $paragraph
     * @return string
     */
    public function fixMarkup(string $paragraph): string
    {
        $paragraph = str_replace('shape="rect"', '', $paragraph); // for some reason iSite does this on links
        $paragraph = str_replace('<ul>', '</p><ul>', $paragraph);
        $paragraph = str_replace('<ol>', '</p><ol>', $paragraph);
        $paragraph = str_replace('</ul>', '</ul><p>', $paragraph);
        $paragraph = str_replace('</ol>', '</ol><p>', $paragraph);
        $paragraph = preg_replace("/^<\/p>/", '', $paragraph);
        $paragraph = preg_replace("/<p>$/", '', $paragraph);
        $paragraph = '<p>' . $paragraph . '</p>';
        $paragraph = preg_replace("/^<p><(ul|ol|h2|h3|h4|h5|h6|p)>/", "<$1>", $paragraph);
        $paragraph = preg_replace("/<(\/ul|\/ol|\/h2|\/h3|\/h4|\/h5|\/h6|\/p)><\/p>$/", '<$1>', $paragraph);
        $paragraph = trim($paragraph);
        return $paragraph;
    }

    public function cleanupText($content): string
    {
        $content = strip_tags($content, '<a><ul><ol><li><strong><em><h2><h3><h4><h5><h6><p><br>');
        $content = str_replace('&nbsp;', ' ', $content); // spaces shouldn't be none-breaking spaces
        $content = trim($content);
        return $content;
    }
}
