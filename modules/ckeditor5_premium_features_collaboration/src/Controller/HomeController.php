<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HomeController extends ControllerBase {

  /**
   * Lists all available articles.
   *
   * @return ResponseInterface
   */
  public function index(): ResponseInterface {
    $stmt = $this->db->query(
      "SELECT a.id as art_id, a.title, u.id as author_id, u.*
                        FROM articles a
                        LEFT JOIN users u ON u.id = a.user_id"
    );

    $articles = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    return $this->view('home', ['articles' => $articles]);
  }

  /**
   * Action that handles logging in.
   *
   * @param Request $req
   * @param Response $res
   * @param array $args
   *
   * @return ResponseInterface
   */
  public function login(Request $req, Response $res, array $args): ResponseInterface {
    $loginError = FALSE;

    if ($req->getMethod() === 'POST') {
      $post = $req->getParsedBody();

      $login = trim((string) $post['login']);
      $password = trim((string) $post['password']);

      $user = $this->getUserRepository()->getUserByLogin($login);

      if (!$user || !password_verify($password, $user['password'])) {
        $loginError = TRUE;
      }
      else {
        $_SESSION['current_user_id'] = (int) $user['id'];
        $_SESSION['csrf_token'] = base64_encode(random_bytes(15));
        $homePath = $this->container->get('router')->pathFor('home');

        return $res->withRedirect($homePath);
      }
    }

    return $this->view('login', ['loginError' => $loginError]);
  }

  /**
   * Action that handles logging out.
   *
   * @param Request $req
   * @param Response $res
   * @param array $args
   *
   * @return ResponseInterface
   */
  public function logout(Request $req, Response $res, array $args): ResponseInterface {
    session_destroy();

    $loginPath = $this->container->get('router')->pathFor('login');

    return $res->withRedirect($loginPath);
  }

}
