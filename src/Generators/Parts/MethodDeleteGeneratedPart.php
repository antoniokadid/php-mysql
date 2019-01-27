<?php

namespace Database\Generators\Parts;

require_once __DIR__ . '/../Definitions.php';

use const Database\Generators\EOL;
use const Database\Generators\TAB;

use Database\Generators\GeneratedPart;

/**
 * Class MethodDeletePart
 *
 * @package Database\Generators\Sections
 */
class MethodDeleteGeneratedPart extends GeneratedPart
{
    /**
     * @return string
     */
    public function process(): string
    {
        $docComment = "";
        $methodName = "delete";
        $methodBody = "";

        $primaryKeys = array_filter(
            $this->tableColumns,
            function (array $column) {
                return $column['primary'] === TRUE;
            });

        // COMMENT
        $docComment .= TAB . "/**" . EOL;
        $docComment .= TAB . " * Delete" . EOL;
        $docComment .= TAB . " * " . EOL;
        $docComment .= TAB . " * @return bool" . EOL;
        $docComment .= TAB . " * @throws" . EOL;
        $docComment .= TAB . " */" . EOL;

        // BODY
        $methodBody .= TAB . "public function {$methodName}(): bool {" . EOL;

        $queryParams = implode(" AND ", array_map(function (array $column) {
            return $column['colName'] . " = ?";
        }, $primaryKeys));

        $methodBody .= TAB . TAB . "\$sql = \"DELETE FROM {$this->tableName} " . EOL .
            TAB . TAB . "        WHERE {$queryParams}\";" . EOL . EOL;

        $queryParams = implode(", ", array_map(function (array $column) {
            $propName = $column['propName'];
            $propType = $column['propType'];
            $colType = $column['colType'];
            $nullable = $column['nullable'];

            if ($propType === '\DateTime')
            {
                if ($colType === 'DATE')
                    return "\$this->{$propName}->format('Y-m-d')";
                else if ($colType === 'TIME')
                    return "\$this->{$propName}->format('H:i:s')";
                else
                    return "\$this->{$propName}->format('Y-m-d H:i:s')";
            }

            return "\$this->{$propName}";
        }, $primaryKeys));

        $methodBody .= TAB . TAB . "return \$this->connection->execute(\$sql, [{$queryParams}]);" . EOL;

        $methodBody .= TAB . "}";

        return $docComment . $methodBody;
    }
}