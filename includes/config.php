<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('SITE_NAME', 'Solidus 3D Modeling');
define('SITE_SHORT_NAME', 'Solidus.3D');
define('SITE_TAGLINE', 'Precision 3D modeling, CAD conversion, prototyping, and manufacturing support for product teams.');
define('SITE_DEFAULT_URL', 'https://solidus3dmodeling.com');
define('SITE_EMAIL', 'info@solidus3dmodeling.com');
define('SUPPORT_EMAIL', 'support@solidus3dmodeling.com');
define('SITE_PHONE', '+91 7420866709');
define('SITE_PHONE_LINK', '+917420866709');
define('SITE_LOCATION', 'Pune, Maharashtra, India');
define('GA_MEASUREMENT_ID', 'GA_MEASUREMENT_ID');

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'solidus3d');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

define('ADMIN_USER', getenv('ADMIN_USER') ?: 'admin');
define('ADMIN_PASS_HASH', '$2y$10$5nQcbro9811VPzl0rrES5O5NKVCkAa4nkeprvQVsFrcFAoNbUMn4q');

define('BLOG_UPLOAD_DIR', dirname(__DIR__) . '/assets/uploads/blog');
define('BLOG_UPLOAD_WEB_PATH', '/assets/uploads/blog');

function base_url(): string
{
    // 1. Honour explicit SITE_URL env variable (production override)
    $configured = getenv('SITE_URL');
    if (is_string($configured) && $configured !== '') {
        return rtrim($configured, '/');
    }

    if (!empty($_SERVER['HTTP_HOST'])) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $scheme . '://' . $_SERVER['HTTP_HOST'];

        // 2. Auto-detect subfolder (e.g. /Solidus.3d on XAMPP)
        //    Compare document root with this file's location.
        $docRoot = isset($_SERVER['DOCUMENT_ROOT'])
            ? rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/')
            : '';
        $appRoot = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..')), '/');

        if ($docRoot !== '' && str_starts_with($appRoot, $docRoot)) {
            $subPath = substr($appRoot, strlen($docRoot)); // e.g. "/Solidus.3d"
            return $host . $subPath;
        }

        return $host;
    }

    return SITE_DEFAULT_URL;
}

function site_url(string $path = ''): string
{
    $path = ltrim($path, '/');
    return $path === '' ? base_url() : base_url() . '/' . $path;
}

function asset_url(string $path): string
{
    return site_url('assets/' . ltrim($path, '/'));
}

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function current_year(): string
{
    return date('Y');
}

function excerpt_text(string $text, int $limit = 170): string
{
    $plain = trim(preg_replace('/\s+/', ' ', strip_tags($text)) ?? '');
    if ($plain === '' || strlen($plain) <= $limit) {
        return $plain;
    }

    $cut = substr($plain, 0, $limit);
    $lastSpace = strrpos($cut, ' ');
    if ($lastSpace !== false) {
        $cut = substr($cut, 0, $lastSpace);
    }

    return rtrim($cut, '., ') . '...';
}

function format_publish_date($date): string
{
    if (!$date) {
        return '';
    }

    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return $date;
    }

    return date('F j, Y', $timestamp);
}

function render_text_blocks(string $content): string
{
    $blocks = preg_split("/\R{2,}/", trim($content)) ?: [];
    if ($blocks === []) {
        return '';
    }

    $html = [];
    foreach ($blocks as $block) {
        $html[] = '<p>' . nl2br(h(trim($block))) . '</p>';
    }

    return implode("\n", $html);
}

function slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    $value = trim($value, '-');
    return $value !== '' ? $value : 'post';
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['csrf_token'];
}

function csrf_is_valid($token): bool
{
    return is_string($token) && $token !== '' && hash_equals(csrf_token(), $token);
}

function flash_set(string $key, string $message, string $type = 'success'): void
{
    $_SESSION['flash_messages'][$key] = [
        'message' => $message,
        'type' => $type,
    ];
}

function flash_get(string $key): ?array
{
    if (empty($_SESSION['flash_messages'][$key])) {
        return null;
    }

    $flash = $_SESSION['flash_messages'][$key];
    unset($_SESSION['flash_messages'][$key]);
    return is_array($flash) ? $flash : null;
}

function admin_is_configured(): bool
{
    return ADMIN_PASS_HASH !== '' && ADMIN_PASS_HASH !== 'paste_result_here';
}

function blog_table_sql(): string
{
    return <<<SQL
CREATE TABLE blog_posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(500) NOT NULL,
  slug VARCHAR(500) UNIQUE NOT NULL,
  content LONGTEXT NOT NULL,
  category VARCHAR(100) DEFAULT '3D Modeling',
  meta_title VARCHAR(255),
  meta_desc VARCHAR(300),
  meta_keywords VARCHAR(500),
  image VARCHAR(300),
  published TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;
}

function site_services(): array
{
    return [
        [
            'group' => 'Engineering and CAD',
            'id' => 'technical-drawings',
            'title' => 'Technical Drawings and Product Design Support',
            'summary' => 'Dimensioned drawings, revision packs, and design refinement for sourcing and production.',
            'description' => 'We prepare technical drawings, documentation sets, and product-support files that help teams move from concept approval to prototype and vendor handoff.',
            'deliverables' => ['2D production drawings', 'Dimension packs', 'Exploded views', 'Revision issue lists'],
        ],
        [
            'group' => 'Engineering and CAD',
            'id' => '2d-to-3d',
            'title' => '2D to 3D Conversion',
            'summary' => 'Convert drawings, sketches, and PDFs into editable 3D models.',
            'description' => 'We translate engineering intent from flat documents into structured 3D geometry that is easier to review, quote, and produce.',
            'deliverables' => ['3D CAD conversion', 'Assembly recreation', 'Drawing updates', 'Revision-ready models'],
        ],
        [
            'group' => 'Engineering and CAD',
            'id' => 'reverse-engineering',
            'title' => 'Reverse Engineering',
            'summary' => 'Rebuild legacy or scanned parts into manufacturable CAD.',
            'description' => 'Our reverse engineering workflow turns worn or undocumented parts into editable CAD that can be validated and remanufactured quickly.',
            'deliverables' => ['Scan cleanup', 'Surface remodeling', 'Tolerance-aware CAD', 'Drawing support'],
        ],
        [
            'group' => 'Engineering and CAD',
            'id' => 'cad-modeling',
            'title' => '3D CAD Modeling',
            'summary' => 'Solid modeling support for new products and custom manufacturing briefs.',
            'description' => 'Inspired by the AddLayers manufacturing stack, this service focuses on editable CAD models built specifically for prototyping and vendor-ready production flows.',
            'deliverables' => ['Solid part modeling', 'Assembly structuring', 'Editable feature trees', 'Vendor handoff packs'],
        ],
        [
            'group' => 'Engineering and CAD',
            'id' => 'design-for-manufacturing',
            'title' => 'Design for Manufacturing Review',
            'summary' => 'Tolerance, geometry, and process-fit checks before release.',
            'description' => 'We review parts for machining, printing, casting, and fabrication constraints so teams can reduce revision loops before requesting quotes or placing orders.',
            'deliverables' => ['Tolerance review', 'Geometry simplification', 'Process notes', 'Revision recommendations'],
        ],
        [
            'group' => 'Engineering and CAD',
            'id' => 'product-visualization',
            'title' => 'Product Rendering and Visualization',
            'summary' => 'Exploded views, still renders, and technical visuals for reviews and launches.',
            'description' => 'From approval decks to product pages, we create render-ready assets that stay grounded in accurate geometry and real engineering intent.',
            'deliverables' => ['Still renders', 'Cutaway visuals', 'Exploded views', 'Launch-ready assets'],
        ],
        [
            'group' => 'Manufacturing and Prototyping',
            'id' => 'custom-manufacturing',
            'title' => 'Custom Manufacturing Support',
            'summary' => 'Bridge design, prototyping, and vendor communication in one workflow.',
            'description' => 'Based on the AddLayers manufacturing service stack, we package the right files and process notes for prototype, bridge, and low-volume production programs.',
            'deliverables' => ['Process-fit guidance', 'Vendor-ready CAD', 'Prototype planning', 'Release packages'],
        ],
        [
            'group' => 'Manufacturing and Prototyping',
            'id' => '3d-printing-service',
            'title' => '3D Printing Service',
            'summary' => 'Functional prototypes and low-volume parts in common print processes.',
            'description' => 'We prepare and support additive workflows for fast concept validation, fit checks, and presentation parts across standard 3D printing processes.',
            'deliverables' => ['FDM / SLA / SLS guidance', 'Prototype parts', 'Fit-check iterations', 'Print-ready exports'],
        ],
        [
            'group' => 'Manufacturing and Prototyping',
            'id' => '3d-print-ready',
            'title' => '3D Print-Ready Models',
            'summary' => 'Watertight files optimized for additive manufacturing.',
            'description' => 'We prepare meshes and solid bodies that print cleanly, reduce iteration waste, and respect real-world machine constraints.',
            'deliverables' => ['STL / OBJ', 'Wall-thickness checks', 'Support planning', 'Print consultation'],
        ],
        [
            'group' => 'Manufacturing and Prototyping',
            'id' => 'cnc-machining',
            'title' => 'CNC Machining',
            'summary' => 'Machinable CAD for milled, turned, and precision CNC components.',
            'description' => 'We prepare production-focused CAD and geometry reviews for CNC machining workflows where tolerances, tool access, and material constraints matter.',
            'deliverables' => ['Machining-ready solids', 'Feature simplification', 'Tolerance review', 'Shop handoff files'],
        ],
        [
            'group' => 'Manufacturing and Prototyping',
            'id' => 'vacuum-casting',
            'title' => 'Vacuum Casting',
            'summary' => 'Bridge-production parts and presentation models using silicone tooling.',
            'description' => 'For low-volume runs and presentation-grade components, we prepare geometry that suits vacuum casting and silicone mold workflows.',
            'deliverables' => ['Split-part planning', 'Master patterns', 'Finish-aware geometry', 'Low-volume production support'],
        ],
        [
            'group' => 'Manufacturing and Prototyping',
            'id' => 'profile-cutting',
            'title' => 'Profile Cutting',
            'summary' => 'Laser, plasma, or router-friendly files for flat and sheet parts.',
            'description' => 'We convert designs into clean profile-cutting files with the geometry discipline needed for sheet-based fabrication workflows.',
            'deliverables' => ['DXF / flat profiles', 'Tab and slot prep', 'Cut path cleanup', 'Fabrication-ready exports'],
        ],
        [
            'group' => 'Manufacturing and Prototyping',
            'id' => 'rapid-prototyping',
            'title' => 'Rapid Prototyping Support',
            'summary' => 'Fast-turn prototype planning across additive, machining, and casting routes.',
            'description' => 'We help teams choose the right prototype path based on geometry, budget, finish, lead time, and the level of validation required.',
            'deliverables' => ['Prototype planning', 'Process selection', 'Iteration strategy', 'Vendor-ready exports'],
        ],
        [
            'group' => 'Manufacturing and Prototyping',
            'id' => 'sheet-metal-fabrication',
            'title' => 'Sheet Metal Fabrication Support',
            'summary' => 'Flat-pattern and bend-aware files for enclosures, panels, and fabricated parts.',
            'description' => 'We prepare sheet metal parts for cut-and-bend workflows with cleaner flat patterns, enclosure logic, and fabrication-ready geometry.',
            'deliverables' => ['Flat patterns', 'Bend-aware layouts', 'Panel assemblies', 'Fabrication-ready exports'],
        ],
        [
            'group' => 'Additional Services',
            'id' => 'architectural-3d-modeling',
            'title' => 'Architectural 3D Modeling',
            'summary' => 'Structured 3D assets for concept review, presentations, and fabrication planning.',
            'description' => 'We support architects and design teams with clean models that work across review meetings, pitch decks, and physical model planning.',
            'deliverables' => ['Exterior massing', 'Interior layouts', 'Presentation scenes', 'Clean model hierarchies'],
        ],
        [
            'group' => 'Additional Services',
            'id' => 'presentation-visualization',
            'title' => 'Presentation Visualization Assets',
            'summary' => 'Render-ready assets for proposals, approvals, and client-facing product communication.',
            'description' => 'When teams need a polished presentation layer, we turn accurate geometry into visuals that help internal approvals, sales decks, and launch materials move faster.',
            'deliverables' => ['Proposal visuals', 'Presentation stills', 'Section graphics', 'Approval-ready assets'],
        ],
    ];
}

function site_service_groups(): array
{
    $groups = [
        'Engineering and CAD' => [
            'title' => 'Engineering and CAD',
            'subtitle' => 'Design intent, editable geometry, and vendor-ready technical files.',
            'description' => 'Use this lane for CAD development, file conversion, reverse engineering, documentation, and pre-production visualization.',
            'services' => [],
        ],
        'Manufacturing and Prototyping' => [
            'title' => 'Manufacturing and Prototyping',
            'subtitle' => 'Additive, subtractive, and bridge-production service support.',
            'description' => 'This group is expanded from the AddLayers manufacturing stack and covers 3D printing, CNC machining, vacuum casting, profile cutting, and production-ready support.',
            'services' => [],
        ],
        'Additional Services' => [
            'title' => 'Additional Services',
            'subtitle' => 'Architectural models, presentation visuals, and adjacent support work.',
            'description' => 'Use this lane for architectural modeling, approval-ready visuals, and support deliverables that complement core manufacturing workflows.',
            'services' => [],
        ],
    ];

    foreach (site_services() as $service) {
        $groups[$service['group']]['services'][] = $service;
    }

    return array_values($groups);
}

function site_capabilities(): array
{
    return [
        'NDA-backed workflows for confidential products',
        'Unlimited revision rounds during active production windows',
        'Delivery support for CNC, casting, molding, and additive manufacturing',
        'Fast coordination with founders, agencies, and in-house engineering teams',
    ];
}

function site_stats(): array
{
    return [
        ['value' => '10+', 'label' => 'years in 3D delivery'],
        ['value' => '24h', 'label' => 'average response window'],
        ['value' => '30+', 'label' => 'countries served'],
        ['value' => 'NDA', 'label' => 'default confidentiality-first process'],
    ];
}

function site_portfolio_items(): array
{
    return [
        [
            'title' => 'Industrial Pump Housing Rebuild',
            'category' => 'Mechanical CAD',
            'summary' => 'Rebuilt a legacy housing from field measurements and damaged reference parts for a maintenance-heavy client.',
            'outcome' => 'Editable CAD, exploded assembly views, and machining-ready export pack.',
        ],
        [
            'title' => 'Consumer Product Launch Visuals',
            'category' => 'Product Visualization',
            'summary' => 'Created polished renders and section views for a pre-production electronics launch deck.',
            'outcome' => 'Launch visuals that helped the team align investors, vendors, and packaging decisions.',
        ],
        [
            'title' => 'Custom Architectural Facade Model',
            'category' => 'Architecture',
            'summary' => 'Modeled a complex facade concept with clean hierarchy for presentation and detail refinement.',
            'outcome' => 'Readable geometry for review meetings, render scenes, and fabrication discussions.',
        ],
        [
            'title' => 'Vacuum Casting Master Pattern Development',
            'category' => 'Vacuum Casting',
            'summary' => 'Prepared a product shell master pattern and split strategy for a low-volume urethane casting run.',
            'outcome' => 'Cleaner pilot-batch parts, faster approvals, and a smoother bridge-to-production workflow.',
        ],
        [
            'title' => '2D Drawing to Manufacturing Assembly',
            'category' => '2D to 3D',
            'summary' => 'Converted paper drawings and PDFs into a full assembly with component-level revisions.',
            'outcome' => 'A digital assembly stack that reduced quote ambiguity and sped up vendor handoff.',
        ],
        [
            'title' => 'Reverse-Engineered Spare Part Program',
            'category' => 'Reverse Engineering',
            'summary' => 'Standardized multiple worn spare parts into a clean CAD library for repeat manufacturing.',
            'outcome' => 'Consistent files, simplified procurement, and less downtime for the client.',
        ],
    ];
}

function sample_blog_posts(): array
{
    return [
        [
            'id' => 1,
            'title' => 'How to Choose the Right 3D File Format for Manufacturing',
            'slug' => 'how-to-choose-the-right-3d-file-format',
            'category' => 'Engineering',
            'meta_title' => 'Choosing the Right 3D File Format for Manufacturing',
            'meta_desc' => 'A practical guide to STEP, IGES, STL, OBJ, and native CAD handoff decisions.',
            'meta_keywords' => 'STEP, IGES, STL, CAD, manufacturing',
            'image' => null,
            'published' => 1,
            'created_at' => '2026-03-08 10:00:00',
            'content' => "The file format you hand to a supplier affects much more than convenience. It changes how easily they can edit geometry, inspect tolerances, and move your design into manufacturing.\n\nFor editable engineering work, STEP is usually the safest default because it preserves solid geometry well across major CAD systems. IGES can still be useful for surface-heavy workflows, but it tends to require more cleanup.\n\nWhen the goal is additive manufacturing, STL remains common because slicers accept it everywhere. The tradeoff is that STL throws away design intelligence and keeps only a mesh, so it is poor for revision-heavy collaboration.\n\nA strong handoff package often includes both the editable CAD exchange file and the print or visualization mesh. That combination gives suppliers enough freedom to review intent without forcing unnecessary remodeling.",
        ],
        [
            'id' => 2,
            'title' => 'Reverse Engineering Legacy Parts Without Slowing Production',
            'slug' => 'reverse-engineering-legacy-parts-without-slowing-production',
            'category' => 'Manufacturing',
            'meta_title' => 'Reverse Engineering Legacy Parts Faster',
            'meta_desc' => 'A practical reverse engineering workflow for outdated or undocumented components.',
            'meta_keywords' => 'reverse engineering, spare parts, CAD reconstruction',
            'image' => null,
            'published' => 1,
            'created_at' => '2026-02-18 14:30:00',
            'content' => "Legacy parts are often discovered at the worst time: during a breakdown, a rush reorder, or an urgent replacement cycle. A calm reverse engineering workflow prevents that pressure from turning into bad geometry.\n\nWe typically start by separating what is critical from what is simply cosmetic. Functional fits, mounting points, sealing surfaces, and tolerance-sensitive regions get captured first. That keeps redesign effort focused where downtime risk actually lives.\n\nIf scan data is available, it helps, but scan data alone is rarely enough. Clean, editable CAD still needs engineering judgment, especially when the original part has wear, deformation, or undocumented fixes.\n\nThe fastest teams pair reconstruction with validation. Even a lightweight review loop with measurements, markups, and manufacturing input can prevent rework later in the process.",
        ],
        [
            'id' => 3,
            'title' => 'Five Checks Before Sending a Model for 3D Printing',
            'slug' => 'five-checks-before-sending-a-model-for-3d-printing',
            'category' => 'Design Tips',
            'meta_title' => 'Five Checks Before 3D Printing a Model',
            'meta_desc' => 'A short checklist for wall thickness, tolerances, orientation, and print success.',
            'meta_keywords' => '3D printing checklist, STL preparation, print-ready model',
            'image' => null,
            'published' => 1,
            'created_at' => '2026-01-22 09:15:00',
            'content' => "A model that looks finished on screen can still fail quickly once it enters a print workflow. Small preparation checks save time, material, and repeated setup.\n\nStart with wall thickness and unsupported details. Fine edges, thin tabs, and deep recesses may look great in CAD but behave differently depending on whether the part is printed in FDM, SLA, or SLS.\n\nThen check orientation and split strategy. Some parts print more cleanly when broken into logical sections rather than forced into a single piece with excessive supports.\n\nFinally, confirm the file is watertight and exported at the right scale. Those two details sound basic, but they still cause avoidable delays in many prototype batches.",
        ],
    ];
}

function blog_categories(): array
{
    return ['All', 'Engineering', 'Manufacturing', 'Design Tips'];
}
