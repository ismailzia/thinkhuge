<?php

namespace App\core;

class Paginator
{
    /**
     * Render pagination HTML with info text and navigation links.
     *
     * @param int   $currentPage   Current page number (1-based)
     * @param int   $totalPages    Total number of pages
     * @param array $queryParams   Additional query parameters for URLs
     * @param int   $window        Number of pages to show on each side of current page
     * @param int   $currentCount  Number of items on the current page
     * @param int   $totalItems    Total number of items across all pages
     * @param int   $perPage       Items per page
     * @return string              Rendered HTML string
     */
    public static function render(
        $currentPage,
        $totalPages,
        $queryParams = [],
        $window = 2,
        $currentCount = 0,
        $totalItems = 0,
        $perPage = 10
    ) {
        // Return empty if no pages or items
        if ($totalPages <= 1 && $totalItems == 0) {
            return '';
        }

        // Calculate display info text
        $firstItem = $totalItems == 0 ? 0 : (($currentPage - 1) * $perPage) + 1;
        $lastItem = $totalItems == 0 ? 0 : min($firstItem + $currentCount - 1, $totalItems);
        $infoText = match (true) {
            $totalItems == 0 => 'No items found.',
            $totalItems <= $perPage => "Showing $totalItems of $totalItems",
            default => "Showing $firstItem – $lastItem of $totalItems"
        };

        // Begin container
        $html = '<div class="d-flex justify-content-between align-items-center flex-wrap mb-2">';
        $html .= '<div class="text-muted small">' . $infoText . '</div>';

        // Pagination nav start
        $html .= '<nav><ul class="pagination mb-0 justify-content-end">';

        // Previous button
        $params = $queryParams;
        $params['page'] = max(1, $currentPage - 1);
        $html .= self::paginationLink('Previous', $params, $currentPage <= 1);

        // Calculate page range window
        [$start, $end] = [max(1, $currentPage - $window), min($totalPages, $currentPage + $window)];

        // Leading first page and ellipsis if needed
        if ($start > 1) {
            $params['page'] = 1;
            $html .= self::paginationLink(1, $params);
            if ($start > 2) {
                $html .= self::ellipsis();
            }
        }

        // Page number links
        for ($i = $start; $i <= $end; $i++) {
            $params['page'] = $i;
            $html .= self::paginationLink($i, $params, false, $i == $currentPage);
        }

        // Trailing ellipsis and last page if needed
        if ($end < $totalPages) {
            if ($end < $totalPages - 1) {
                $html .= self::ellipsis();
            }
            $params['page'] = $totalPages;
            $html .= self::paginationLink($totalPages, $params);
        }

        // Next button
        $params['page'] = min($totalPages, $currentPage + 1);
        $html .= self::paginationLink('Next', $params, $currentPage >= $totalPages);

        // Close nav and container
        $html .= '</ul></nav>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render a single pagination link.
     *
     * @param string|int $label Text or number to show
     * @param array $params Query parameters for link URL
     * @param bool $disabled Whether link is disabled
     * @param bool $active Whether link is active (current page)
     * @return string HTML list item for pagination
     */
    protected static function paginationLink($label, $params, $disabled = false, $active = false)
    {
        $classes = 'page-item';
        if ($disabled) {
            $classes .= ' disabled';
        }
        if ($active) {
            $classes .= ' active';
        }
        $url = '?' . http_build_query($params);
        return "<li class=\"$classes\"><a class=\"page-link\" href=\"$url\">$label</a></li>";
    }

    /**
     * Render an ellipsis for skipped pages.
     *
     * @return string HTML list item with ellipsis
     */
    protected static function ellipsis()
    {
        return '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
}
