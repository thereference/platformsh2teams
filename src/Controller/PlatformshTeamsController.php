<?php

namespace Drupal\platformsh_teams\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\platformsh_teams\Cards\PlatformshCard;
use Sebbmyr\Teams\TeamsConnector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * PlatformshTeamsController.
 */
class PlatformshTeamsController extends ControllerBase {

  /**
   * Function that parses PlatformSH json and sends it to Teams.
   */
  public function webhook(Request $request) {

    // Incoming JSON.
    $platformsh = json_decode($request->getContent());
    if (empty($platformsh)) {
      throw new NotFoundHttpException();
    }

    // Some default variables.
    $text = FALSE;
    $color = '008000';
    $endpoint = 'ADD TEAMS ENDPOINT HERE';

    // Some frequently used variables.
    $name = $platformsh->payload->user->display_name;
    $branch = $platformsh->payload->environment->name;
    $project_string = $platformsh->payload->environment->project;
    $commits_count_str = $platformsh->payload->commits_count;

    // Define text output based on PlatformSH notification type.
    switch ($platformsh->type) {
      case 'environment.activate':
        $text = "$name activated environment for branch `$branch` of $project_string";
        break;

      case 'environment.update.http_access':
        $text = "$name changed HTTP Authentication settings on environment `$branch` of $project_string";
        break;

      case 'environment.access.add':
        $text = "$name added {$platformsh->payload->access->display_name} to `$branch` of $project_string";
        break;

      case 'environment.access.remove':
        $text = "$name removed {$platformsh->payload->access->display_name} from `$branch` of $project_string";
        break;

      case 'environment.redeploy':
        $text = "$name redeployed environment `$branch` of $project_string";
        break;

      case 'environment.synchronize':
        $parent = $platformsh->parameters->from;
        $child = $platformsh->parameters->into;
        if ($platformsh->parameters->synchronize_code) {
          $payload[] = '*code*';
        }
        if ($platformsh->parameters->synchronize_data) {
          $payload[] = '*data & files*';
        }
        $payload = implode(', ', $payload);

        $text = "$name synchronized $payload from `$parent` into `$child` environment of $project_string";
        break;

      case 'environment.push':
        $text = "$name pushed $commits_count_str to branch `$branch` of $project_string";
        break;

      case 'environment.branch':
        $text = "$name created a branch `$branch` of $project_string";
        break;

      case 'environment.delete':
        $text = "$name deleted the branch `$branch` of $project_string";
        $color = 'FF0000';
        break;

      case 'environment.merge':
        $text = "$name merged branch `{$platformsh->parameters->from}` into `{$platformsh->parameters->into}` of $project_string";
        break;

      case 'environment.subscription.update':
        $text = "$name updated the subscription of $project_string";
        break;

      case 'project.domain.create':
      case 'project.domain.update':
        $text = "$name updated domain `{$platformsh->payload->domain->name}` of $project_string";
        break;

      case 'environment.backup':
        $text = "$name created the snapshot `{$platformsh->payload->backup_name}` from `$branch` of $project_string";
        break;

      case 'environment.deactivate':
        $text = "$name deactivated the environment `$branch` of $project_string";
        break;

      case 'environment.variable.create':
        $text = "$name created variable `{$platformsh->payload->variable->name}` on $project_string";
        break;

      case 'environment.variable.update':
        $text = "$name updated variable `{$platformsh->payload->variable->name}` on $project_string";
        break;

      case 'environment.variable.delete':
        $text = "$name deleted variable `{$platformsh->payload->variable->name}` on $project_string";
        break;

      default:
        $text = "$name triggered an unhandled webhook `{$platformsh->type}` to branch `$branch` of $project_string";
        break;
    }

    $connector = new TeamsConnector($endpoint);
    $card = new PlatformshCard([
      'text' => $text,
      'themeColor' => $color,
    ]);
    $connector->send($card);
    throw new NotFoundHttpException();
  }

}
