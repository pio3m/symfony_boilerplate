<?php
interface ReportGeneratorInterface
{
    public function generate(array $tasks): string;
}

class JsonReportGenerator implements ReportGeneratorInterface
{
    public function generate(array $tasks): string
    {
        return json_encode($tasks);
    }
}

class CsvReportGenerator implements ReportGeneratorInterface
{
    public function generate(array $tasks): string
    {
        $csv = fopen('php://temp', 'r+');
        foreach ($tasks as $row) {
            fputcsv($csv, $row);
        }
        rewind($csv);
        return stream_get_contents($csv);
    }
}

class ReportFactory
{
    public static function create(string $format): ReportGeneratorInterface
    {
        return match($format) {
            'json' => new JsonReportGenerator(),
            'csv'  => new CsvReportGenerator(),
            default => throw new InvalidArgumentException("Unknown format: $format"),
        };
    }
}

$tasks = [['id'=>1, 'title'=>'Kup mleko'], ['id'=>2, 'title'=>'Zrób prezentację']];

$generator = ReportFactory::create('csv');
echo $generator->generate($tasks);

