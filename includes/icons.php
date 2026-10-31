<?php
/**
 * OMAH BELUMBUNGAN — Premium SVG Icon System
 * Balinese Heritage × Modern Minimalism × Natural Elegance
 * 
 * Standard viewBox: 0 0 24 24
 * Stroke-width: 1.65 (standard UI icon)
 * Colors: currentColor (controlled via CSS / tokens)
 */

if (!defined('OB_ICONS_LOADED')) {
    define('OB_ICONS_LOADED', true);

    function ob_get_icon_svg(string $name): string {
        static $icons = null;
        if ($icons === null) {
            $icons = [
                // ==========================================
                // 1. Core UI & System Icons (Lucide Standard)
                // ==========================================
                'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line>',
                'clock' => '<circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline>',
                'map-pin' => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle>',
                'phone' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>',
                'mail' => '<rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>',
                'message-circle' => '<path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"></path>',
                'user' => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle>',
                'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>',
                'car' => '<path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9C2.1 11.2 2 11.6 2 12v4c0 .6.4 1 1 1h2"></path><circle cx="7" cy="17" r="2"></circle><path d="M9 17h6"></path><circle cx="17" cy="17" r="2"></circle>',
                'bus' => '<path d="M8 6v6"></path><path d="M15 6v6"></path><path d="M2 12h19.6"></path><path d="M18 18h3s.5-1.7.8-2.8c.1-.4.2-.8.2-1.2V6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v8c0 .4.1.8.2 1.2.3 1.1.8 2.8.8 2.8h3"></path><circle cx="6.5" cy="18.5" r="2.5"></circle><circle cx="16.5" cy="18.5" r="2.5"></circle>',
                'compass' => '<circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon>',
                'navigation' => '<polygon points="3 11 22 2 13 21 11 13 3 11"></polygon>',
                'search' => '<circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>',
                'camera' => '<path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle>',
                'zoom-in' => '<circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line>',
                'check' => '<polyline points="20 6 9 17 4 12"></polyline>',
                'check-circle' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline>',
                'alert-triangle' => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>',
                'info' => '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line>',
                'arrow-right' => '<line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline>',
                'chevron-left' => '<polyline points="15 18 9 12 15 6"></polyline>',
                'chevron-right' => '<polyline points="9 18 15 12 9 6"></polyline>',
                'external-link' => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line>',
                'ticket' => '<path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"></path><path d="M13 5v2"></path><path d="M13 17v2"></path><path d="M13 11v2"></path>',
                'star' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>',
                'bar-chart' => '<line x1="12" y1="20" x2="12" y2="10"></line><line x1="18" y1="20" x2="18" y2="4"></line><line x1="6" y1="20" x2="6" y2="16"></line>',
                'settings' => '<circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>',
                'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>',
                'shield-check' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="m9 12 2 2 4-4"></path>',
                'sparkles' => '<path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"></path>',

                // ==========================================
                // 2. Amenities & Property Facilities
                // ==========================================
                'wifi' => '<path d="M5 12.55a11 11 0 0 1 14.08 0"></path><path d="M1.42 9a16 16 0 0 1 21.16 0"></path><path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path><line x1="12" y1="20" x2="12.01" y2="20"></line>',
                'wind' => '<path d="M17.7 7.7a2.5 2.5 0 1 1 1.8 4.3H2"></path><path d="M9.6 4.6A2 2 0 1 1 11 8H2"></path><path d="M12.6 19.4A2 2 0 1 0 14 16H2"></path>',
                'bed' => '<path d="M2 4v16"></path><path d="M2 8h18a2 2 0 0 1 2 2v10"></path><path d="M2 17h20"></path><path d="M6 8v9"></path>',
                'bath' => '<path d="M9 6 6.5 3.5a1.5 1.5 0 0 0-2.12 0 1.5 1.5 0 0 0 0 2.12L6.88 8"></path><path d="M4 21h16"></path><path d="M2 12h20v4a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5Z"></path>',
                'parking' => '<circle cx="12" cy="12" r="10"></circle><path d="M9 17V7h4a3 3 0 0 1 0 6H9"></path>',
                'utensils' => '<path d="M18 2v6a3 3 0 0 1-3 3 3 3 0 0 1-3-3V2"></path><path d="M15 11v11"></path><path d="M5 2v20"></path><path d="M2 2h6"></path><path d="M2 6h6"></path>',
                'coffee' => '<path d="M17 8h1a4 4 0 1 1 0 8h-1"></path><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"></path><line x1="6" y1="2" x2="6" y2="4"></line><line x1="10" y1="2" x2="10" y2="4"></line><line x1="14" y1="2" x2="14" y2="4"></line>',
                'tree' => '<path d="M12 19v3"></path><path d="M12 19a7 7 0 1 0 0-14 7 7 0 0 0 0 14Z"></path><path d="M12 9a3 3 0 0 0-3 3"></path>',
                'maximize' => '<path d="M8 3H5a2 2 0 0 0-2 2v3"></path><path d="M21 8V5a2 2 0 0 0-2-2h-3"></path><path d="M3 16v3a2 2 0 0 0 2 2h3"></path><path d="M16 21h3a2 2 0 0 0 2-2v-3"></path>',
                'log-in' => '<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line>',
                'log-out' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line>',

                // ==========================================
                // 3. Heritage & Balinese Architecture Icons
                // ==========================================
                // Angkul-angkul (Gerbang Tradisional Beratap)
                'angkul-angkul' => '<path d="M3 21h18"></path><path d="M5 21V10h14v11"></path><path d="M2 10l10-7 10 7"></path><path d="M8 21v-7h8v7"></path><path d="M12 3v4"></path>',
                // Candi Bentar (Gerbang Terbelah Khas Bali)
                'candi-bentar' => '<path d="M2 21h20"></path><path d="M4 21V9l4-5v17"></path><path d="M20 21V9l-4-5v17"></path><path d="M4 14h4"></path><path d="M16 14h4"></path><path d="M4 10h4"></path><path d="M16 10h4"></path>',
                // Bale (Paviliun Terbuka Bertiang Saka)
                'bale' => '<path d="M2 9l10-6 10 6"></path><path d="M3 21h18"></path><path d="M4 17h16v4H4z"></path><line x1="6" y1="9" x2="6" y2="17"></line><line x1="10" y1="9" x2="10" y2="17"></line><line x1="14" y1="9" x2="14" y2="17"></line><line x1="18" y1="9" x2="18" y2="17"></line>',
                // Pemerajan (Pelinggih / Meru Suci Bertingkat)
                'pemerajan' => '<path d="M5 21h14"></path><path d="M7 21v-4h10v4"></path><path d="M4 17l8-5 8 5"></path><path d="M6 12l6-4 6 4"></path><path d="M8 8l4-5 4 5"></path><line x1="12" y1="3" x2="12" y2="1"></line>',
                // Natah (Halaman Komunal / Keseimbangan Mandala)
                'natah' => '<rect x="3" y="3" width="18" height="18" rx="2"></rect><circle cx="12" cy="12" r="4"></circle><line x1="12" y1="3" x2="12" y2="8"></line><line x1="12" y1="16" x2="12" y2="21"></line><line x1="3" y1="12" x2="8" y2="12"></line><line x1="16" y1="12" x2="21" y2="12"></line>',
                // Padi / Bulir Padi Kearifan Agraris Bali
                'padi' => '<path d="M4 22C4 13 12 5 21 3"></path><path d="M11 15a4 4 0 0 1 4-4c0 2-2 4-4 4z"></path><path d="M14 11a4 4 0 0 1 4-4c0 2-2 4-4 4z"></path><path d="M17 7a4 4 0 0 1 4-4c0 2-2 4-4 4z"></path><path d="M7 19a4 4 0 0 1 4-4c0 2-2 4-4 4z"></path>',
                // Padma / Bunga Teratai Spiritual
                'lotus' => '<path d="M12 21c0-5 5-9 9-9-4 8-9 9-9 9z"></path><path d="M12 21c0-5-5-9-9-9 4 8 9 9 9 9z"></path><path d="M12 21c-2-6-1-12 0-16 1 4 2 10 0 16z"></path><path d="M12 21c2-4 5-7 7-6-1 4-4 6-7 6z"></path><path d="M12 21c-2-4-5-7-7-6 1 4 4 6 7 6z"></path>',
                // Bata Bali (Material Pres Bata Merah & Paras)
                'bata-bali' => '<rect x="3" y="4" width="18" height="5" rx="1"></rect><rect x="3" y="10" width="8" height="5" rx="1"></rect><rect x="13" y="10" width="8" height="5" rx="1"></rect><rect x="3" y="16" width="18" height="5" rx="1"></rect>',

                // ==========================================
                // 4. Social & Communication Brands
                // ==========================================
                'instagram' => '<rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>',
                'youtube' => '<path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon>',
                'facebook' => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>',
                'whatsapp' => '<path fill="currentColor" stroke="none" d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.634.053-1.077-.089-.47-.151-1.077-.42-1.85-1.089-.99-.857-1.636-1.923-1.884-2.349-.247-.426-.027-.656.12-.862.13-.182.288-.376.432-.564.116-.151.154-.257.23-.427.078-.171.039-.32-.02-.439-.058-.119-.526-1.268-.72-1.737-.189-.456-.381-.394-.526-.402-.135-.007-.289-.009-.443-.009-.154 0-.404.058-.615.289-.211.231-.808.789-.808 1.923s.827 2.231.942 2.385c.115.154 1.627 2.485 3.942 3.484 2.316.999 2.316.666 2.738.627.423-.039 1.365-.558 1.558-1.097.192-.539.192-1.001.135-1.098-.058-.096-.212-.154-.442-.269z"/><path fill="currentColor" stroke="none" d="M12 2C6.477 2 2 6.477 2 12c0 1.891.524 3.661 1.435 5.176L2 22l4.981-1.393A9.957 9.957 0 0 0 12 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18.062a8.04 8.04 0 0 1-4.328-1.258l-.31-.186-2.955.827.838-2.883-.203-.324A8.026 8.026 0 0 1 3.938 12c0-4.446 3.616-8.062 8.062-8.062s8.062 3.616 8.062 8.062-3.616 8.062-8.062 8.062z"/>'
            ];
        }

        return $icons[$name] ?? '';
    }

    /**
     * Render an accessible, responsive SVG icon
     * 
     * @param string $name Name of the icon
     * @param array $options Options array:
     *      - 'class': extra CSS classes on the wrapper
     *      - 'size': numeric px (e.g. 18) or token ('xs', 'sm', 'md', 'lg', 'xl')
     *      - 'color': CSS color token or hex ('primary', 'dark', 'warm', 'light', 'white', 'terracotta', etc.)
     *      - 'stroke_width': float (default 1.65)
     *      - 'label': accessible label for screen readers (if absent, aria-hidden="true" is set)
     *      - 'raw': return only <svg> without span wrapper if true
     * @return string Validated HTML SVG output
     */
    function ob_icon(string $name, array $options = []): string {
        $path = ob_get_icon_svg($name);
        if (!$path) {
            return '';
        }

        $size = $options['size'] ?? 20;
        $width = is_numeric($size) ? (int)$size : 20;
        $height = $width;

        if (is_string($size) && in_array($size, ['xs', 'sm', 'md', 'lg', 'xl'], true)) {
            $sizeMap = ['xs' => 14, 'sm' => 16, 'md' => 20, 'lg' => 24, 'xl' => 32];
            $width = $height = $sizeMap[$size];
        }

        $strokeWidth = $options['stroke_width'] ?? '1.65';
        $label = $options['label'] ?? null;
        $accAttr = $label ? 'role="img" aria-label="' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '"' : 'aria-hidden="true"';

        $isBrandSolid = in_array($name, ['whatsapp'], true);
        $fill = $isBrandSolid ? 'currentColor' : 'none';
        $stroke = $isBrandSolid ? 'none' : 'currentColor';
        $strokeAttr = $isBrandSolid ? '' : ' stroke="' . $stroke . '" stroke-width="' . $strokeWidth . '" stroke-linecap="round" stroke-linejoin="round"';

        $svg = '<svg width="' . $width . '" height="' . $height . '" viewBox="0 0 24 24" fill="' . $fill . '"' . $strokeAttr . ' ' . $accAttr . '>' . $path . '</svg>';

        if (!empty($options['raw'])) {
            return $svg;
        }

        $classes = ['ob-icon'];
        if (!empty($options['class'])) {
            $classes[] = $options['class'];
        }
        if (!empty($options['color'])) {
            $classes[] = 'ob-icon-' . $options['color'];
        }

        return '<span class="' . implode(' ', $classes) . '">' . $svg . '</span>';
    }
}
