<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Book;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Build a simple, valid 2-page PDF: a title page and one content page.
 *
 * @param string[] $page1Lines
 * @param string[] $page2Lines
 */
function makeTwoPagePdf(array $page1Lines, array $page2Lines): string
{
    $escape = fn (string $line): string => str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);

    $stream = function (array $lines, int $titleSize) use ($escape): string {
        $out = "BT\n/F1 {$titleSize} Tf\n72 700 Td\n(".$escape(array_shift($lines)).") Tj\n/F1 14 Tf\n";
        foreach ($lines as $line) {
            $out .= "0 -28 Td\n(".$escape($line).") Tj\n";
        }

        return $out."ET\n";
    };

    $content1 = $stream($page1Lines, 32);
    $content2 = $stream($page2Lines, 22);

    $objects = [
        "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
        "2 0 obj\n<< /Type /Pages /Kids [3 0 R 5 0 R] /Count 2 >>\nendobj\n",
        "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 7 0 R >> >> >>\nendobj\n",
        "4 0 obj\n<< /Length ".strlen($content1)." >>\nstream\n{$content1}endstream\nendobj\n",
        "5 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 6 0 R /Resources << /Font << /F1 7 0 R >> >> >>\nendobj\n",
        "6 0 obj\n<< /Length ".strlen($content2)." >>\nstream\n{$content2}endstream\nendobj\n",
        "7 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n",
    ];

    $pdf = "%PDF-1.4\n";
    $offsets = [];
    foreach ($objects as $object) {
        $offsets[] = strlen($pdf);
        $pdf .= $object;
    }

    $xref = strlen($pdf);
    $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
    foreach ($offsets as $offset) {
        $pdf .= sprintf("%010d 00000 n \n", $offset);
    }

    return $pdf."trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF\n";
}

/**
 * Simple SVG cover: accent background, framed, with wrapped title + author.
 */
function makeSvgCover(string $title, string $author, string $accent): string
{
    $esc = fn (string $s): string => htmlspecialchars($s, ENT_XML1);

    $words = explode(' ', $title);
    $lines = [''];
    foreach ($words as $word) {
        $candidate = trim(end($lines).' '.$word);
        if (mb_strlen($candidate) > 14 && end($lines) !== '') {
            $lines[] = $word;
        } else {
            $lines[key($lines)] = $candidate;
        }
    }

    $tspans = '';
    $startY = 400 - (count($lines) - 1) * 34;
    foreach ($lines as $i => $line) {
        $y = $startY + $i * 68;
        $tspans .= '<text x="300" y="'.$y.'" font-family="Georgia, serif" font-size="56" font-weight="bold" fill="#ffffff" text-anchor="middle">'.$esc($line).'</text>';
    }

    return '<svg xmlns="http://www.w3.org/2000/svg" width="600" height="900" viewBox="0 0 600 900">'
        .'<rect width="600" height="900" fill="'.$esc($accent).'"/>'
        .'<rect width="600" height="900" fill="url(#g)"/>'
        .'<linearGradient id="g" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ffffff" stop-opacity="0.12"/><stop offset="1" stop-color="#000000" stop-opacity="0.25"/></linearGradient>'
        .'<rect x="36" y="36" width="528" height="828" fill="none" stroke="#ffffff" stroke-opacity="0.45" stroke-width="3"/>'
        .$tspans
        .'<text x="300" y="790" font-family="Georgia, serif" font-size="28" fill="#ffffff" fill-opacity="0.9" text-anchor="middle">'.$esc($author).'</text>'
        .'</svg>';
}

$categories = [
    'Fantasy' => Category::firstOrCreate(['slug' => 'fantasy'], ['name' => 'Fantasy']),
    'Mystery' => Category::firstOrCreate(['slug' => 'mystery'], ['name' => 'Mystery']),
    'Science Fiction' => Category::firstOrCreate(['slug' => 'science-fiction'], ['name' => 'Science Fiction']),
    'Romance' => Category::firstOrCreate(['slug' => 'romance'], ['name' => 'Romance']),
    'Non-fiction' => Category::firstOrCreate(['slug' => 'non-fiction'], ['name' => 'Non-fiction']),
];

$books = [
    [
        'title' => 'The Little Book of Tea',
        'author' => 'Mira Holloway',
        'price' => 4.99,
        'category' => 'Non-fiction',
        'accent' => '#0f766e',
        'description' => "A tiny, warm-hearted guide to brewing a proper cup of tea.\n\nTwo pages. One ritual. Zero excuses for sad, lukewarm tea. This is the sample book of the store — short enough to read while the kettle boils, and the perfect file to test your library downloads with.",
        'page2' => [
            'Chapter One - The Ritual',
            'Boil fresh water. Never reboil yesterday\'s regrets.',
            'Warm the pot. Cold porcelain steals heat and joy alike.',
            'One spoon of leaves per cup, plus one for the pot.',
            'Steep for three minutes. Patience is the secret ingredient.',
            'Pour, sit somewhere comfortable, and do absolutely nothing else.',
            '',
            'The End. (Told you it was little.)',
        ],
    ],
    [
        'title' => 'The Cartographer of Lost Doors',
        'author' => 'Elias Vance',
        'price' => 12.99,
        'category' => 'Fantasy',
        'accent' => '#7c3aed',
        'description' => "Every city hides doors that lead nowhere — until someone maps them.\n\nNadia Qureshi has spent ten years charting doors that should not exist. When one of them starts charting her back, she must decide whether a map is a record of the world or a promise to it.",
        'page2' => [
            'Chapter One - The Door on Halvard Street',
            'The door had no building attached to it, which was rude,',
            'Nadia thought, even by the standards of impossible doors.',
            'It stood alone between a bakery and a bank, frame and',
            'hinges and brass handle, holding up nothing but morning fog.',
            'She unfolded her map and began to draw.',
        ],
    ],
    [
        'title' => 'A Quiet Death in Larkspur',
        'author' => 'Imogen Hale',
        'price' => 9.49,
        'category' => 'Mystery',
        'accent' => '#b45309',
        'description' => "The village of Larkspur has one cafe, one church, and one murder it refuses to talk about.\n\nRetired detective June Okafor moved to Larkspur for the silence. The silence, it turns out, is the clue.",
        'page2' => [
            'Chapter One - Arrival',
            'June Okafor had interviewed four hundred liars in her career,',
            'and the village of Larkspur greeted her like all of them at once:',
            'warmly, and with something carefully left out.',
        ],
    ],
    [
        'title' => 'Orbital Lullaby',
        'author' => 'Theo Brandt',
        'price' => 11.99,
        'category' => 'Science Fiction',
        'accent' => '#1d4ed8',
        'description' => "The last station above a sleeping Earth plays a song every night. Nobody remembers who programmed it.\n\nMaintenance engineer Sol Ferreira has 90 days of air, one cat, and a melody that seems to be counting down to something.",
        'page2' => [
            'Chapter One - Night Cycle 1,204',
            'The lullaby began at 22:00 station time, as it always did,',
            'three notes rising, two falling, played to an audience of',
            'one engineer, one cat, and eight billion sleepers below.',
        ],
    ],
    [
        'title' => 'The Recipe for Running Away',
        'author' => 'Pia Castellanos',
        'price' => 8.99,
        'category' => 'Romance',
        'accent' => '#be185d',
        'description' => "Dani inherited her grandmother's food truck, a route map, and a note: 'Drive. The rest is seasoning.'\n\nAt the first stop she meets the one person she swore she'd never see again — who happens to be the best line cook on the coast.",
        'page2' => [
            'Chapter One - First Stop',
            'The truck smelled of cumin and old promises.',
            'Dani taped the route map to the dashboard, turned the key,',
            'and drove toward the one town she had sworn off forever.',
        ],
    ],
];

foreach ($books as $data) {
    $slug = Str::slug($data['title']);

    $pdf = makeTwoPagePdf(
        [$data['title'], 'by '.$data['author'], '', 'A sample e-book for testing.'],
        $data['page2'],
    );
    $filePath = 'ebooks/'.$slug.'.pdf';
    Storage::disk('private')->put($filePath, $pdf);

    $coverPath = 'covers/'.$slug.'.svg';
    Storage::disk('public')->put($coverPath, makeSvgCover($data['title'], $data['author'], $data['accent']));

    $book = Book::updateOrCreate(
        ['slug' => $slug],
        [
            'title' => $data['title'],
            'author' => $data['author'],
            'description' => $data['description'],
            'price' => $data['price'],
            'category_id' => $categories[$data['category']]->id,
            'cover_path' => $coverPath,
            'file_path' => $filePath,
            'file_format' => 'pdf',
            'accent_color' => $data['accent'],
            'status' => 'published',
        ],
    );

    echo ($book->wasRecentlyCreated ? 'created' : 'updated').": {$book->title} (/books/{$book->slug})".PHP_EOL;
}

echo PHP_EOL.'total published books: '.Book::published()->count().PHP_EOL;
