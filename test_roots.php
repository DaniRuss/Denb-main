<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$html = view('filament.pages.outbox')->render();
$parser = new \Livewire\Features\SupportMultipleRootElementDetection\HtmlParser;
$nodes = $parser::parse($html);
$elements = array_filter($nodes, fn ($node) => $node instanceof \Livewire\Features\SupportMultipleRootElementDetection\ElementNode);
echo "Count: " . count($elements) . "\n";
foreach($elements as $el) {
    echo $el->tagName . "\n";
}
