<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Controller;

use Baraja\DiffGenerator\SimpleDiff;
use Caxy\HtmlDiff\HtmlDiff;
use DiffMatchPatch\DiffMatchPatch;
use Drupal\ckeditor5_premium_features\Diff\Renderer\Ckeditor5DiffBasicRenderer;
use Drupal\Component\Diff\DiffFormatter;
use Drupal\Component\Diff\MappedDiff;
use Drupal\Component\Diff\WordLevelDiff;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Render\RendererInterface;
use jblond\Diff;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\DiffOnlyOutputBuilder;
use SebastianBergmann\Diff\Output\StrictUnifiedDiffOutputBuilder;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PhpDiffController extends ControllerBase {

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   */
  public function __construct(
    protected RendererInterface $renderer,
    protected RequestStack $requestStack
  ) {
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
      $container->get('request_stack')
    );
  }

  /**
   * API endpoint for rendering media tags.
   *
   * @param string $format
   *   Text editor format.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse|\Symfony\Component\HttpFoundation\JsonResponse
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function index(Request $request) {

    $resp = [
      '<style> body > h2.lib {color: red} </style>',
      '<style> .ck-suggestion-marker-insertion {background-color: green} </style>',
      '<style> .ck-suggestion-marker-deletion {background-color: red} </style>',
    ];

    $left = mb_strtolower($this->sampleA());
    $right = mb_strtolower($this->sampleB());


    $leftArray = explode(PHP_EOL, str_replace('</p>', '</p>' . PHP_EOL, $left));
    $rightArray = explode(PHP_EOL, str_replace('</p>', '</p>' . PHP_EOL, $right));


//
//    $resp[] = '<br /><br /><h2 class="lib">PHP spec</h2><br />';
//
//    // Options for generating the diff
//    $options = array(
//      'ignoreWhitespace' => true,
//      'ignoreCase' => true,
//    );
//
//    // Initialize the diff class
//    $diff = new \Diff([$left], [$right], $options);
//
//    $resp[] = '<h2>Side by Side Diff</h2>';
//
//    // Generate a side by side diff
//    $renderer = new \Diff_Renderer_Html_SideBySide();
//    $resp[] = $diff->Render($renderer);
//
//    $resp[] = '<h2>Inline Diff</h2>';
//
//    // Generate an inline diff
//    $renderer = new \Diff_Renderer_Html_Inline();
//    $resp[] = $diff->render($renderer);
//
//    $resp[] = '<h2>Unified Diff</h2>
//    <pre>';
//
//      // Generate a unified diff
//      $renderer = new \Diff_Renderer_Text_Unified();
//    $resp[] = htmlspecialchars($diff->render($renderer));
//
//    $resp[] = '		</pre>
//    <h2>Context Diff</h2>
//    <pre>';
//
//    // Generate a context diff
//    $renderer = new \Diff_Renderer_Text_Context();
//    $resp[] = htmlspecialchars($diff->render($renderer));







//    $resp[] = '<br /><br /><h2 class="lib">Sebastian </h2><br />';
//
//
//    $builder = new UnifiedDiffOutputBuilder(
//      "--- Original\n+++ New\n", // custom header
//      false                      // do not add line numbers to the diff
//    );
//    $differ = new Differ($builder);
//    $resp[]  = $differ->diff($left, $right);
//
//    $builder = new StrictUnifiedDiffOutputBuilder([
//      'collapseRanges'      => true, // ranges of length one are rendered with the trailing `,1`
//      'commonLineThreshold' => 6,    // number of same lines before ending a new hunk and creating a new one (if needed)
//      'contextLines'        => 3,    // like `diff:  -u, -U NUM, --unified[=NUM]`, for patch/git apply compatibility best to keep at least @ 3
//      'fromFile'            => '',
//      'fromFileDate'        => null,
//      'toFile'              => '',
//      'toFileDate'          => null,
//    ]);
//    $differ = new Differ($builder);
//    $resp[] = $differ->diff($left, $right);
//
//    $builder = new DiffOnlyOutputBuilder(
//      "--- Original\n+++ New\n"
//    );
//    $differ = new Differ($builder);
//    $resp[] =  $differ->diff($left, $right);






    $resp[] = '<br /><br /><h2 class="lib">Caxy\HtmlDiff</h2><br />';


    $htmlDiff = new HtmlDiff($left, $right);
    $resp[] = $htmlDiff->build();








    $resp[] = '<br /><br /><h2 class="lib">yetanotherape/diff-match-patch</h2><br />';


    $matchDiff = new DiffMatchPatch();
    $matchdiffs = $matchDiff->diff_main($left, $right);
    $resp[] = $matchDiff->diff_prettyHtml($matchdiffs);






    $resp[] = '<br /><br /><h2 class="lib">J BLond</h2><br />';

    // Options for generating the diff.
    $options = [
      'ignoreWhitespace' => true,
      'ignoreCase'       => true,
      'context'          => 0,
      'cliColor'         => true, // for cli output
      'ignoreLines'      => Diff::DIFF_IGNORE_LINE_BLANK,
    ];

    // Initialize the diff class.
    $diff = new Diff($left, $right,  $options);

    // Options for rendering the diff.
    $rendererOptions = [
      'inlineMarking' => Diff\Renderer\MainRenderer::CHANGE_LEVEL_CHAR,
      'insertMarkers' => [
        '<span class="ck-suggestion-marker ck-suggestion-marker-insertion">',
        '</span>'
      ],
      'deleteMarkers' => [
        '<span class="ck-suggestion-marker ck-suggestion-marker-deletion">',
        '</span>'
      ],
    ];

    $renderersBlond[] = new Diff\Renderer\Html\SideBySide($rendererOptions);
    $renderersBlond[] = new Diff\Renderer\Html\Unified($rendererOptions);
    $rendererOptions = [
      'inlineMarking' => Diff\Renderer\MainRenderer::CHANGE_LEVEL_WORD,
    ];
    $renderersBlond[] = new Diff\Renderer\Html\Merged($rendererOptions);
//    $renderersBlond[] = new Diff\Renderer\Text\Unified($rendererOptions);
//    $rendererOptions = [
//      'inlineMarking' => Diff\Renderer\MainRenderer::CHANGE_LEVEL_CHAR,
//      'insertMarkers' => ['#>', '<#'],
//    ];
//    $renderersBlond[] = new Ckeditor5DiffBasicRenderer($rendererOptions);




    foreach ($renderersBlond as $rb) {
      $tr = $diff->Render($rb);
      $resp[] =  htmlspecialchars_decode($tr) ;
      $resp[] = '<hr />';
    }




    $resp[] = '<hr /> Custom:';



    /** @var \Drupal\ckeditor5_premium_features\Diff\Ckeditor5DiffBasic $differ */
    $differ = \Drupal::service('ckeditor5_premium_features_notifications.diff_basic');
    $difference = $differ->getDiff($left, $right);
    $resp[] =  $difference;
    $resp[] = '<hr />';
    $resp[] = $differ->getDiffContext();








    foreach ($resp as &$r) {
      $r = htmlspecialchars_decode($r);
    }

    $resp = new HtmlResponse(implode(PHP_EOL . PHP_EOL,$resp));
    $cm = new CacheableMetadata();
    $cm->setCacheMaxAge(0);
    $resp->addCacheableDependency($cm);

    return $resp;
  }

  function sampleA() {

    return 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed rhoncus mauris vel bibendum laoreet. Praesent ultricies sapien non nisl faucibus, sit amet scelerisque ligula aliquet. Suspendisse convallis mauris elit, non malesuada odio ultrices sit amet. Morbi orci mauris, tincidunt a leo vitae, malesuada auctor ipsum. Fusce sed venenatis enim. Mauris mollis efficitur leo in condimentum. Vivamus eu blandit libero. Pellentesque vitae finibus nisl. Praesent at lacinia tellus, eu finibus enim. Proin tristique mi sit amet elementum lobortis. Quisque id porttitor nisl, a ornare est. Aliquam tristique nulla nisl, eget ultrices enim accumsan vel.

Nunc non urna eu lacus dapibus faucibus. Praesent eu feugiat elit. Fusce pretium dictum velit, eu consectetur orci accumsan ut. Curabitur commodo aliquam felis ut ornare. Sed lobortis diam nisi, ut lacinia neque luctus vel. Duis eget eros vel neque convallis pharetra. Nam a tristique urna, eu vulputate ipsum. Vivamus leo nibh, convallis ut leo semper, convallis egestas augue. Etiam cursus tellus in rhoncus euismod. Duis bibendum ipsum a neque vehicula accumsan. Nam ac pulvinar est. Phasellus feugiat ligula et mauris sodales interdum. Etiam molestie, purus gravida lobortis porttitor, est ipsum commodo nunc, quis tincidunt metus enim non erat. Donec sit amet consequat sapien, vitae vulputate nisi.

Maecenas euismod aliquam quam, non pellentesque mi. In sollicitudin viverra mauris ut malesuada. Duis sollicitudin turpis ac leo tristique, non laoreet eros efficitur. Pellentesque id ornare neque, in sollicitudin dolor. Sed malesuada tristique neque non sollicitudin. Maecenas ut lacus ex. Nullam risus risus, iaculis volutpat elementum non, sodales eu dui. In quis auctor nisi. Nunc et diam ac metus molestie vestibulum.

Phasellus faucibus nulla at fringilla vehicula. Cras a elementum urna. Etiam convallis volutpat nunc, nec suscipit nibh convallis sed. Etiam vel efficitur lectus. Aenean tristique consequat ullamcorper. Donec sit amet enim a felis ultricies accumsan. In sed pellentesque felis. Aliquam sollicitudin ultricies consequat. Cras imperdiet purus sit amet diam semper, non iaculis nisl sollicitudin. Donec posuere purus vel neque egestas aliquet. Suspendisse potenti.

';
    return '<p>porównanie czas start tutuaj tekst do <span class="mention" data-mention="#jakis-test@example.com">#jakis-test@example.com</span> &nbsp;XYZ</p><p>to jest nowy tekst, <span data-mention="#jakis-test@example.com"><span class="mention" data-mention="#jakis-test@example.com">#jakis-test@example.com</span></span> &nbsp;modyfikacja który będziemy modyfikowali.</p><p>oto trzeci paragraf.</p><p>no i podsumowanie :)&nbsp;</p>';
  }

  function sampleB() {

    return 'Lorem ipsum dolor sit amet, adddasdasd elit. Sed rhoncus mauris vel bibendum laoreet. Praesent ultricies sapien non nisl faucibus, sit amet scelerisque ligula aliquet. Suspendisse convallis mauris elit, non malesuada odio ultrices sit amet. Morbi orci mauris, tincidunt a leo vitae, malesuada auctor ipsum. Fusce sed venenatis enim. Mauris mollis efficitur leo in condimentum. Vivamus eu blandit libero. Pellentesque vitae finibus nisl. Praesent at lacinia tellus, eu finibus enim. Proin tristique mi sit amet elementum lobortis. Quisque id porttitor nisl, a ornare est. Aliquam tristique nulla nisl, eget ultrices enim accumsan vel.

Nunc non urna eu lacus dapibus faucibus. Praesent eu feugiat elit. Fusce pretium dictum velit, eu consectetur orci accumsan ut. Curabitur commodo aliquam felis ut ornare. Sed lobortis diam nisi, ut lacinia neque luctus vel. Duis eget eros vel neque convallis pharetra. Nam a tristique urna, eu vulputate ipsum. Vivamus leo nibh, convallis ut leo semper, convallis egestas augue. Etiam cursus tellus in rhoncus euismod. Duis bibendum ipsum a neque vehicula accumsan. Nam ac pulvinar est. Phasellus feugiat ligula et mauris sodales interdum. Etiam molestie, purus gravida lobortis porttitor, est ipsum commodo nunc, quis tincidunt metus enim non erat. Donec sit amet consequat sapien, vitae vulputate nisi.

Maecenas euismod aliquam quam, non pellentesque mi. In sollicitudin fafasdasd malesuada. Duis sollicitudin turpis ac leo tristique, non laoreet eros efficitur. Pellentesque id ornare neque, in sollicitudin dolor. Sed malesuada tristique neque non sollicitudin. Maecenas ut lacus ex. Nullam risus risus, iaculis volutpat elementum non, sodales eu dui. In quis auctor nisi. Nunc et diam ac metus molestie vestibulum.

Phasellus faucibus nulla at fringilla vehicula. Cras a elementum urna. Etiam convallis volutpat nunc, nec suscipit nibh convallis sed. Etiam vel efficitur lectus. Aenean tristique consequat ullamcorper. Donec sit amet enim a felis ultricies accumsan. In sed pellentesque felis. Aliquam sollicitudin ultricies consequat. Cras imperdiet purus sit amet diam semper, non iaculis nisl sollicitudin. Donec posuere purus vel neque egestas aliquet. Suspendisse potenti.

';

    return '<p>porównanie czas start XXXXXX do <span class="mention" data-mention="#jakis-test@example.com">#jakis-test@example.com</span> &nbsp;XYZ</p><p>to jest nowy tekst, <span data-mention="#jakis-test@example.com"><span class="mention" data-mention="#jakis-test@example.com">#jakis-test@example.com</span></span> &nbsp;modyfikacja który będziemy modyfikowali.</p><p>oto cos tam takiego: XXYY trzeci paragraf.</p><p>no i podsumowanie :)&nbsp;</p>';

  }
}
