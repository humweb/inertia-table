<?php

namespace Humweb\InertiaTable\Export;


use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;


class QueryExport implements FromQuery, WithHeadings
{
    use Exportable;

    protected $query;

    /**
     * @var mixed|null
     */
    public mixed $headers;

    /**
     * @param $query
     * @param $headers
     */
    public function __construct($query, $headers = null)
    {
        $this->query   = $query;
        $this->headers = $headers;
    }


    /**
     * @param array $headers
     *
     * @return $this
     */
    public function headers($headers = [])
    {
        $this->headers = $headers;
        return $this;
    }


    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return $this->headers;
    }
}
