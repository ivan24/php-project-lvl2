<?php

namespace Differ;

use Docopt\Response;

function genDiff(Response $params): string
{
    return sprintf(
        "\nCompute diff between \n\t%s \n\t %s\nFormat: %s\n\n",
        $params->args['<firstFile>'],
        $params->args['<secondFile>'],
        $params->args['--format']
    );
}
