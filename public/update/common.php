<?php
defined('GIT_PATH') || exit;

function get($name, $default = null)
{
    if (isset($_POST[$name])) return $_POST[$name];
    if (isset($_GET[$name])) return $_GET[$name];
    return $default;
}

function session($name, $value = null)
{
    if (isset($_SESSION['_UPDATE_TOOL'][$name])) $old = $_SESSION['_UPDATE_TOOL'][$name];
    else $old = $value;
    if (isset($value))
        $_SESSION['_UPDATE_TOOL'][$name] = $value;
    return $old;
}

function isDir($filename)
{
    if (!is_dir($filename))
        return mkdir($filename, 755, true);
    return true;
}

function users($users = null)
{
    if (!is_null($users)) {
        if (isDir('data'))
            file_put_contents('data/users.php', '<?php return ' . var_export($users, true) . ';');
    } elseif (file_exists('data/users.php')) {
        $users = require 'data/users.php';
    }
    return $users;
}

function tags($tags = null)
{
	if (!is_null($tags)) {
		if (isDir('data'))
			file_put_contents('data/tags.php', '<?php return ' . var_export($tags, true) . ';');
	} elseif (file_exists('data/tags.php')) {
		$tags = require 'data/tags.php';
	}
	return $tags;
}

function branch($branch = null)
{
    if (!is_null($branch)) {
        if (isDir('data'))
            file_put_contents('data/branch.php', '<?php return ' . var_export($branch, true) . ';');
    } elseif (file_exists('data/branch.php')) {
        $branch = require 'data/branch.php';
    }
    return $branch;
}

function versions($versions = null)
{
    if (!is_null($versions)) {
        if (isDir('data'))
            file_put_contents('data/versions.php', '<?php return ' . var_export($versions, true) . ';');
    } elseif (file_exists('data/versions.php')) {
        $versions = require 'data/versions.php';
    }
    return $versions;
}

function getTagData($name, $data)
{
	preg_match('/commit\s+(\w{40})\n/i', $data, $hash);
	if (isset($hash[1])) $hash = $hash[1];
	else $hash = '';

	preg_match('/Tagger:\s+([^\n]+)\n/i', $data, $author);
	if (isset($author[1])) $author = htmlentities($author[1]);
	else $author = '';

	preg_match('/Date:\s+([^\n]+)\n/i', $data, $date);
	if (isset($date[1])) $date = $date[1];
	else $date = '';

	$data = preg_split('/Date:\s+([^\n]+)\n|commit\s+(\w{40})\n/i', $data);
	if (isset($data[1])) $comment = trim($data[1]);
	else $comment = '';

	return array(
		'current' => false,
		'object' => false,
		'hash' => $hash,
		'name' => $name,
		'author' => $author,
		'date' => $date, //date('Y-m-d H:i:s', strtotime($date))
		'time' => strtotime($date),
		'comment' => htmlentities($comment)
	);
}

function getBranchData($name, $data)
{
	preg_match('/commit\s+(\w{40})\n/i', $data, $hash);
	if (isset($hash[1])) $hash = $hash[1];
	else $hash = '';

	preg_match('/Author:\s+([^\n]+)\n/i', $data, $author);
	if (isset($author[1])) $author = htmlentities($author[1]);
	else $author = '';

	preg_match('/Date:\s+([^\n]+)\n/i', $data, $date);
	if (isset($date[1])) $date = $date[1];
	else $date = '';

	$data = preg_split('/Date:\s+([^\n]+)\n|commit\s+(\w{40})\n/i', $data);
	if (isset($data[1])) $comment = trim($data[1]);
	else $comment = '';

	return array(
		'current' => false,
		'object' => false,
		'hash' => $hash,
		'name' => $name,
		'author' => $author,
		'date' => $date, //date('Y-m-d H:i:s', strtotime($date))
		'time' => strtotime($date),
		'comment' => htmlentities($comment)
	);
}

function updated($updated = null)
{
    if (!is_null($updated)) {
        if (isDir('data'))
            file_put_contents('data/updated.php', 'return ' . var_export($updated)) . ';';
    } elseif (file_exists('data/updated.php')) {
        $updated = require 'data/updated.php';
    }
    return $updated;
}

function launch($command, $background = false)
{
    if (function_exists('exec')) {
        $last_line = exec($command, $output, $return_var);
        $output = implode("\n", $output);
    } elseif (function_exists('system')) {
        ob_start();
        $last_line = system($command, $return_var);
        $output = ob_get_contents();
        ob_end_clean();
    } elseif (function_exists('passthru')) {
        ob_start();
        passthru($command, $return_var);
        $output = ob_get_contents();
        ob_end_clean();
    }
    //echo '<pre>';var_dump($output);echo '</pre>';
    return $output;

    /*
    // Windows
    if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
        if ($background) {
            pclose(popen('start "background_exec" ' . $call, 'r'));
        } else {
            pclose(popen('start ' . $call, 'r'));
        }
    } // Some sort of UNIX
    else {
        if ($background) {
            exec($call . ' > /dev/null &');
        } else {
            exec($call);
        }
    }
    return true;
    */
}

/*####################################################*/

function start_revision($current_revision)
{
    if (file_exists('data/start_revision.txt')) {
        $start_revision = trim(file_get_contents('data/start_revision.txt'));
        if ($start_revision) return $start_revision;
    }

    if (isDir('data'))
        file_put_contents('data/start_revision.txt', $current_revision);
    return $current_revision;
}

function almost_branch($current_branch)
{
    if (file_exists('data/almost_branch.txt')) {
        $almost_branch = trim(file_get_contents('data/almost_branch.txt'));
        if ($almost_branch) return $almost_branch;
    }

    file_put_contents('data/almost_branch.txt', $current_branch);
    return $current_branch;
}

/**
 * Hien khong dung ham nay
 *
 * @param $log
 *
 * @return array
 */
function logAllToRevision($log)
{
    $revisions = array();
    if ($log != "" && preg_match_all('/commit\s+(\w{40})\n/i', $log, $matches)) {
        $data = preg_split('/commit\s+\w{40}\n/i', $log);
        foreach ($matches[1] as $k => $match) {
            preg_match('/Author:\s+([^\n]+)\n/i', $data[$k + 1], $author);
            if (isset($author[1])) {
                $author = htmlentities($author[1]);
                $data[$k + 1] = preg_replace('/Author:\s+[^\n]+\n/i', '', $data[$k + 1]);
            } else $author = '';

            preg_match('/Date:\s+([^\n]+)\n/i', $data[$k + 1], $date);
            if (isset($date[1])) {
                $date = $date[1];
                $data[$k + 1] = preg_replace('/Date:\s+[^\n]+\n/i', '', $data[$k + 1]);
            } else $date = '';

            $comment = trim($data[$k + 1]);

            $revisions[$match] = array(
                'hash' => $match,
                'author' => $author,
                'date' => $date, //date('Y-m-d H:i:s', strtotime($date))
                'comment' => $comment
            );
        }
    }
    return $revisions;
}

function logTagToTag($name, $log)
{
    static $updated = false;
    if (false === $updated) $updated = updated();

    preg_match('/commit\s+(\w{40})\n/i', $log, $hash);
    if (isset($hash[1])) $hash = $hash[1];
    else $hash = '';

    preg_match('/Tagger:\s+([^\n]+)\n/i', $log, $author);
    if (isset($author[1])) $author = htmlentities($author[1]);
    else $author = '';

    preg_match('/Date:\s+([^\n]+)\n/i', $log, $date);
    if (isset($date[1])) $date = $date[1];
    else $date = '';

    $log = preg_split('/Date:\s+([^\n]+)\n|commit\s+(\w{40})\n/i', $log);
    if (isset($log[1])) $comment = trim($log[1]);
    else $comment = '';

    $status = 0;
    $note = '';
    if (isset($updated[$name])) {

    } else {
        $explorer = launch(GIT_PATH . " show $hash:" . GIT_VERSION_PATH . $name);
        if (trim($explorer)) {
            if (false !== strpos($explorer, 'note.txt')) {
                $status += VERSION_STATUS_AVAILABLE;
                $note = launch(GIT_PATH . ' show remotes/origin/HEAD:' . GIT_VERSION_PATH . "$name/note.txt");
            }
            if (false !== strpos($explorer, 'update.php'))
                $status += VERSION_STATUS_SCRIPT;
            if (false !== (strpos($explorer, 'update.sql') || strpos($explorer, 'update.sql.zip')))
                $status += VERSION_STATUS_DATABASE;
            if (false !== strpos($explorer, 'revert.php'))
                $status += VERSION_STATUS_UN_SCRIPT;
            if (false !== (strpos($explorer, 'revert.sql') || strpos($explorer, 'revert.sql.zip')))
                $status += VERSION_STATUS_UN_DATABASE;
            if ($status) $status |= VERSION_STATUS_AVAILABLE;
        }
    }

    return array(
        'object' => true,
        'hash' => $hash,
        'name' => $name,
        'author' => $author,
        'date' => $date, //date('Y-m-d H:i:s', strtotime($date))
        'status' => $status,
        'note' => $note,
        'comment' => htmlentities($comment)
    );
}

function loadRevisionFromFile($nodes, $start_revision)
{
    $revisions = array();
    if (file_exists('data/git_log.txt') && is_readable('data/git_log.txt')) {
        $handle = fopen('data/git_log.txt', 'r');
        while (($line = fgets($handle)) !== false) {
            if (preg_match('/^(commit|Author|Date)\:?\s+/i', $line, $matches)) {
                switch (strtolower($matches[1])) {
                    case 'commit':
                        if (isset($revision)) {
                            $revision['comment'] = trim($revision['comment']);
                            $revisions[$revision['hash']] = $revision;
                        }

                        if (!$nodes && !$start_revision) break 2;

                        $hash = trim(preg_replace('/commit\s+/i', '', $line));

                        if ($start_revision == $hash) $start_revision = false;

                        $revision = array(
                            'hash' => trim(preg_replace('/commit\s+/i', '', $line)),
                            'author' => '',
                            'date' => '',
                            'comment' => '',
                            'nodes' => getNodeByHash($hash, $nodes, true)
                        );

                        break;
                    case 'author':
                        $revision['author'] = htmlentities(trim(preg_replace('/Author\:\s+/i', '', $line)));
                        break;
                    case 'date':
                        $revision['date'] = trim(preg_replace('/Date\:\s+/i', '', $line));
                        break;
                }
            } else {
                $revision['comment'] .= "$line\n";
            }
        }
        fclose($handle);
    }
    return $revisions;
}

function getNodeByHash($hash, &$nodes, $remove = false)
{
    $result = array();
    foreach ($nodes as $key => &$node) {
        if ($node['hash'] == $hash) {
            $result[] = $node;
            if ($remove) unset($nodes[$key]);
        }
    }
    return $result;
}