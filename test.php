<?php

error_reporting(E_ALL);
ini_set('assert.exception', 1);
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

const DEBUG = true;

require_once __DIR__ . '/vendor/autoload.php';

$source = <<<EOF

%token BATATA
%token<i> INT
%token<s> VAR
%type<i> expr
 
%right '='
%left '+' '-'
%left '*' '/' '%'
%right BATATA

%%

list: /* empty */
    | list stmt
    ;
 
stmt: expr ','
    | expr ':'          { std::cout << $1 << std::endl; }
    ;
 
expr: INT               { $$ = $1; }
    | VAR               { $$ = vars[*$1];      delete $1; }
    | VAR '=' expr      { $$ = vars[*$1] = $3; delete $1; }
    | expr '+' expr     { $$ = $1 + $3; }
    | expr '-' expr     { $$ = $1 - $3; }
    | expr '*' expr     { $$ = $1 * $3; }
    | expr '/' expr     { $$ = $1 / $3; }
    | expr '%' expr     { $$ = $1 % $3; }
    | '+' expr  %prec BATATA    { $$ =  $2; }
    | '-' expr  %prec BATATA    { $$ = -$2; }
    | '(' expr ')'              { $$ =  $2; }
    ;

%%
EOF;


$lexer = new PhpYacc\Yacc\Lexer();
$macroset = new PhpYacc\Yacc\MacroSet;

$parser = new PhpYacc\Yacc\Parser($lexer, $macroset);

$parseResult = $parser->parse($source, "test.php");

/*foreach ($parseResult->grams() as $g) {
    echo $g->body, "\n";
}*/

$generator = new PhpYacc\Lalr\Generator;

$lalrResult = $generator->compute($parseResult);

