<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Video extends CI_Controller {
	function rootdir() {
		return array(
			"/applications/record/files/REC01/tv/MIDIA",
		);
	}

	function srcdir($source) {
		return "/applications/record/files/".$source."/tv/MIDIA";
	}

	function destfile($file) {
		$filenarr = explode("_", $file);
		$source = $filenarr[0];
		$date = $filenarr[1];
		$datearr = explode("-", $filenarr[1]);
		$year = $datearr[0];
		$month = $datearr[1];
		$dateObj = DateTime::createFromFormat('!m', $month);
		$monthName = $dateObj->format('F');
		$day = $datearr[2];

		$time = $filenarr[1];
		$channel = $filenarr[3];
		$state = $filenarr[4];

		$rootdir = $this->srcdir($source);
		$filename = str_replace($source."_", "",$file);
		return $destfile = $rootdir."/".$month."-".$monthName."/".$date."/".$channel."_".$state."/".$filename.".mp4";
	}

	function joindestfile($file) {
		$filenarr = explode("_", $file);
		$source = $filenarr[0];
		$date = $filenarr[1];
		$datearr = explode("-", $filenarr[1]);
		$year = $datearr[0];
		$month = $datearr[1];
		$dateObj = DateTime::createFromFormat('!m', $month);
		$monthName = $dateObj->format('F');
		$day = $datearr[2];

		$time = $filenarr[1];
		$channel = $filenarr[3];
		$state = $filenarr[4];

		$rootdir = $this->joinDir();
		$filename = str_replace($source."_", "",$file);
		return $destfile = $rootdir."/".$filename;
	}

	function thumbrootdir() {
		return $rootdir = array(
			"/applications/record/files/REC01/tv/THUMB"
		);
	}

	function thumbsrcdir($source) {
		return "/applications/record/files/".$source."/tv/THUMB";
	}

	function thumbdestfile($file,$num) {
		$filenarr = explode("_", $file);
		$source = $filenarr[0];
		$date = $filenarr[1];
		$datearr = explode("-", $filenarr[1]);
		$year = $datearr[0];
		$month = $datearr[1];
		$dateObj = DateTime::createFromFormat('!m', $month);
		$monthName = $dateObj->format('F');
		$day = $datearr[2];

		$time = $filenarr[1];
		$channel = $filenarr[3];
		$state = $filenarr[4];

		$thumbrootdir = $this->thumbsrcdir($source);
		$filename = str_replace($source."_", "",$file);
		$foldername = str_replace($source."_", "",$file);
		return $thumbrootdir."/".$month."-".$monthName."/".$date."/".$channel."_".$state."/".$foldername."/".$filename."_".$num.".jpg";
	}

	function cropDir() {
		$cropDir = '/tv/CROP/';
		if (!file_exists($cropDir)) {
			mkdir($cropDir, 0777, true);
		}
		return $cropDir;
	}

	function joinDir() {
		$joinDir = '/tv/JOIN/';
		if (!file_exists($joinDir)) {
			mkdir($joinDir, 0777, true);
		}
		return $joinDir;
	}

	function cropLogDir() {
		$cropLogDir = '/tv/LOG/CROP/';
		if (!file_exists($cropLogDir)) {
			mkdir($cropLogDir, 0777, true);
		}
		return $cropLogDir;
	}

	function joinLogDir() {
		$joinLogDir = '/tv/LOG/JOIN/';
		if (!file_exists($joinLogDir)) {
			mkdir($joinLogDir, 0777, true);
		}
		return $joinLogDir;
	}

	public function index() {
		$message = "Please, select one option!";
		header('Access-Control-Allow-Origin: *');
		header('Content-Type: application/json');
		print json_encode($message);
	}

	public function getchannels($date) {
		$datesp = explode("-", $date);
		$year = $datesp[0];
		$month = $datesp[1];
		$dateObj = DateTime::createFromFormat('!m', $month);
		$monthName = $dateObj->format('F');
		$day = $datesp[2];

		$rootdirs = $this->rootdir();
		foreach ($rootdirs as $dir) {
			$chdir = $dir."/".$month."-".$monthName."/".$date."/";
			$sourcearr = explode("/", $dir);
			$source = $sourcearr[4];
			$func = function($var) {
					$charr = explode("/", $var);
					$channel = $charr[9];
					return $channel;
			};
			$channels[$source] = array_map($func, glob($chdir."*", GLOB_ONLYDIR));
		}

		header('Access-Control-Allow-Origin: *');
		header('Content-Type: application/json');
		print json_encode($channels);
	}

	public function getdates($source) {
		$rootdir = $this->srcdir($source);
		$monthdirs = glob($rootdir.'/*', GLOB_ONLYDIR);
		foreach ($monthdirs as $monthd) {
			$monthdates = glob($monthd.'/*', GLOB_ONLYDIR);
			$monthdname = preg_replace("/.*\/MIDIA\//", "", $monthd);
			$monthddayname = preg_replace("/.*\/$monthdname\//", "", $monthdates);
			$sourcedates[$monthdname] = $monthddayname;
		}

		header('Access-Control-Allow-Origin: *');
		header('Content-Type: application/json');
		print json_encode($sourcedates);
	}

	public function getlist() {
		$channel = $this->input->get("channel");
		$date = $this->input->get("date");
		$datesp = explode("-", $date);
		$year = $datesp[0];
		$month = $datesp[1];
		$dateObj = DateTime::createFromFormat('!m', $month);
		$monthName = $dateObj->format('F');
		$day = $datesp[2];

		$rootdir = $this->srcdir($this->input->get("source"));
		$destdir = $rootdir."/".$month."-".$monthName."/".$date."/".$channel."/";

		$filesindir = array_map('basename', glob($destdir.'*.{mp4,MP4}', GLOB_BRACE));
		// $filesindir = glob($destdir.'*.{mp4,MP4}', GLOB_BRACE);
		header('Access-Control-Allow-Origin: *');
		header('Content-Type: application/json');
		print json_encode($filesindir);
	}

	public function getvideo($file) {
		$destfile = $this->destfile($file);

		if (file_exists($destfile)) {
			header('Access-Control-Allow-Origin: *');
			header("X-Sendfile: $destfile");
			header('Content-Type: video/mp4');
			header('Content-Disposition: attachment; filename="'.basename($destfile).'"');
		} else {
			header("HTTP/1.1 404 Not Found");
		}
	}

	public function getthumb($file, $num){
		$thumbdestfile = $this->thumbdestfile($file, $num);
		// var_dump($thumbdestfile);

		if (file_exists($thumbdestfile)) {
			header('Access-Control-Allow-Origin: *');
			header('X-Sendfile: '.$thumbdestfile);
			header('Content-Type: image/jpg');
		} else {
			header("HTTP/1.1 404 Not Found");
			// $message = "File not found!";
			// print json_encode($message, JSON_PRETTY_PRINT);
		}
	}

	public function cropvideo($file, $start, $dur) {
		$cropDir = $this->cropDir();
		$cropLogDir = $this->cropLogDir();

		$ffmpegpath = "/usr/bin/ffmpeg";
		$src = explode("_", $file);
		$source = $src[0];
		$destfile = $this->destfile($file);
		$start = str_replace("-", ":", $start);
		// $end = str_replace("-", ":", $end);
		$dur = str_replace("-", ":", $dur);
		$now = strtotime("now");
		$message["id"] = $now;
		$message['cropfilename'] = $file."_".$now."_crop.mp4";

		if ($source == 'cagiva01' or $source == 'cagiva02') {
			$message['execcrop'] = $ffmpegpath." -ss ".$start." -i ".$destfile." -t ".$dur." -c:v libx264 -preset ultrafast -crf 26 -vf yadif -y ".$this->cropDir().$message['cropfilename'];
		} else if ($source == 'vespa' or $source == 'honda01' or $source == 'honda02' or $source == 'honda03' or $source == 'buell04' or $source == 'buell05') {
			$message['execcrop'] = $ffmpegpath." -ss ".$start." -i ".$destfile." -t ".$dur." -preset veryfast -r 30 -vf yadif -y ".$this->cropDir().$message['cropfilename'];
		} else {
			$message['execcrop'] = $ffmpegpath." -ss ".$start." -i ".$destfile." -t ".$dur." -preset veryfast -y ".$this->cropDir().$message['cropfilename'];
		}

		putenv("FFREPORT=file=".$cropLogDir.$now.".log:level=32");
		exec("/bin/bash -c \"".$message['execcrop']." 1> /dev/null 2>/dev/null &\"", $message['execlog'], $message['execreturn']);

		header('Access-Control-Allow-Origin: *');
		header('Content-Type: application/json');
		print json_encode($message);
	}

	public function getcropvideo($file, $vifile = null) {
		$destfile = $this->cropDir().$file;

		if (is_null($vifile)) {
			$downdestfile = $destfile;
		} else {
			$vinhetadir = "/mnt/bmw/VIDEOS/VINHETAS/";
			$vifile = $vifile.".mp4";
			$vinhetafile = $vinhetadir.$vifile;

			$filesdir = "/tv/LOG/JOINFILES";
			$now = strtotime("now");
			$concatfile = $filesdir."/".$now."_files.txt";
			file_put_contents($concatfile, "file '".$vinhetafile."'\n", FILE_APPEND);
			file_put_contents($concatfile, "file '".$destfile."'\n", FILE_APPEND);

			$ffmpegpath = "/usr/bin/ffmpeg";
			$joindestfile = str_replace('.mp4', '_v.mp4', $destfile);
			// $execjoin = $ffmpegpath." -f concat -safe 0 -i ".$concatfile." -c:v libx264 -preset ultrafast -crf 30 -y ".$joindestfile;
			// $execjoin = $ffmpegpath." -f concat -safe 0 -i ".$concatfile." -c copy -y ".$joindestfile;
			// $execjoin = $ffmpegpath.' -i '.$vinhetafile." -i ".$destfile." -filter_complex \"[0:v][0:a][1:v][1:a] concat=n=2:v=1:a=1 [v] [a]\" -map \"[v]\" -map \"[a]\" ".$joindestfile;
			$execjoin = $ffmpegpath." -i ".$vinhetafile." -i ".$destfile." -filter_complex \"[0:v:0][0:a:0][1:v:0][1:a:0]concat=n=2:v=1:a=1[outv][outa]\" -map \"[outv]\" -map \"[outa]\" -y ".$joindestfile;
			putenv("FFREPORT=file=/tmp/log_teste.log:level=50");
			exec("/bin/bash -c \"".$execjoin." 1> /dev/null 2>/dev/null\"");
			// exec($execjoin);

			$downdestfile = $joindestfile;
		}

		if (file_exists($downdestfile)) {
			header('Access-Control-Allow-Origin: *');
			header("X-Sendfile: $downdestfile");
			header('Content-Type: video/mp4');
			header('Content-Disposition: attachment; filename="'.basename($downdestfile).'"');
		} else {
			header("HTTP/1.1 404 Not Found");
		}
	}

	public function verifycropvideo($file) {
		$destfile = $this->cropDir().$file;

		if (file_exists($destfile)) {
			header('Access-Control-Allow-Origin: *');
			header('Content-Type: application/json');
			$message = "OK";
			print json_encode($message);
		} else {
			header("HTTP/1.1 404 Not Found");
		}
	}

	public function joinvideos() {
		$joinLogDir = $this->joinLogDir();
		$postdata = ($_POST = json_decode(file_get_contents("php://input"),true));
		$vsource = $postdata['vsource'];
		$files = $postdata['files'];
		$message['files'] = array();
		$firstfile = $files[0];
		$lastindex = count($files) - 1;
		$lastfile = $files[$lastindex];

		$firstfilearr = explode("_", str_replace(".mp4", "", $firstfile));
		$firstfiledate = $firstfilearr[0]."_".$firstfilearr[1];
		$firstfilename = $firstfilearr[2]."_".$firstfilearr[3];

		$lastfilearr = explode("_", str_replace(".mp4", "", $lastfile));
		$lastfiledate = $lastfilearr[0]."_".$lastfilearr[1];
		$lastfilename = $lastfilearr[2]."_".$lastfilearr[3];

		$now = strtotime("now");
		$message["id"] = $now;
		$ffmpegpath = "/usr/bin/ffmpeg";
		$destdir = $this->joinDir();
		$filesdir = "/tv/LOG/JOINFILES";
		$message['joinfilename'] = $vsource."_".$firstfiledate."_to_".$lastfiledate."_".$firstfilename."_".$now."_join.mp4";

		$concatfile = $filesdir."/".$now."_files.txt";
		$concatdur = array();
		foreach ($files as $file) {
			$execout = array();
			$dstf = $vsource."_".str_replace(".mp4", "", $file);
			$destfile = $this->destfile($dstf);

			exec("/bin/bash -c \"ffmpeg -i ".$destfile." 2>&1 | grep Duration | sed 's/,//g'\"", $execout);
			$execoutarr = explode(" ", $execout[0]);
			$fileDuration = $execoutarr[3];

			$far = array_reverse(explode(":", $fileDuration));
			$fduration = floatval($far[0]);
			if (!empty($far[1])) $fduration += intval($far[1]) * 60;
			if (!empty($far[2])) $fduration += intval($far[2]) * 60 * 60;

			$tmpf["file"] = $dstf;
			$tmpf["time"] = round($fduration);
			array_push($message['files'], $tmpf);

			array_push($concatdur, $fduration);
			file_put_contents($concatfile, "file '".$destfile."'\n", FILE_APPEND);
		}

		// $message['totaltime'] = floor(array_sum($concatdur));
		$message['totaltime'] = array_sum($concatdur);

		if ($vsource == 'cagiva01' or $vsource == 'cagiva02') {
			// $message['execjoin'] = $ffmpegpath." -f concat -safe 0 -i ".$concatfile." -c:v libx264 -preset ultrafast -vf yadif -y ".$destdir.$message['joinfilename'];
			//$message['execjoin'] = $ffmpegpath." -f concat -safe 0 -i ".$concatfile." -c:v copy -c:a aac -y ".$destdir.$message['joinfilename'];
			$message['execjoin'] = $ffmpegpath." -f concat -safe 0 -i ".$concatfile." -c copy -y ".$destdir.$message['joinfilename'];
		} else {
			$message['execjoin'] = $ffmpegpath." -f concat -safe 0 -i ".$concatfile." -c copy -y ".$destdir.$message['joinfilename'];
		}

		putenv("FFREPORT=file=".$this->joinLogDir().$now.".log:level=32");
		exec("/bin/bash -c \"".$message['execjoin']." 1> /dev/null 2>/dev/null &\"", $message['execlog'], $message['execreturn']);

		header('Access-Control-Allow-Origin: *');
		header('Content-Type: application/json');
		print json_encode($message);
	}

	public function joincropvideos() {
		$postdata = ($_POST = json_decode(file_get_contents("php://input"),true));
		$vsource = $postdata['vsource'];
		$message['files'] = $postdata['files'];
		$firstfile = $message['files'][0];

		$firstfilearr = explode("_", str_replace(".mp4", "", $firstfile));
		$firstfiledate = $firstfilearr[1]."_".$firstfilearr[2];
		$firstfilename = $firstfilearr[3]."_".$firstfilearr[4];

		$now = strtotime("now");
		$message["id"] = $now;
		$ffmpegpath = "/usr/bin/ffmpeg";
		$destdir = $this->joinDir();
		$filesdir = "/tv/LOG/JOINFILES";
		$message['joinfilename'] = $firstfiledate."_".$firstfilename."_".$now."_join.mp4";

		$concatfile = $filesdir."/".$now."_files.txt";
		$concatdur = array();
		$sdir = $this->cropDir();
		$ccount=0;
		foreach ($message['files'] as $mfile) {
			$execout = array();
			exec("/bin/bash -c \"ffmpeg -i ".$sdir.$mfile." 2>&1 | grep Duration | sed 's/,//g'\"", $execout, $execlog);
			$execoutarr = explode(" ", $execout[0]);
			$fileDuration = $execoutarr[3];

			$far = array_reverse(explode(":", $fileDuration));
			$fduration = floatval($far[0]);
			if (!empty($far[1])) $fduration += intval($far[1]) * 60;
			if (!empty($far[2])) $fduration += intval($far[2]) * 60 * 60;

			array_push($concatdur, $fduration);
			file_put_contents($concatfile, "file '".$sdir.$mfile."'\n", FILE_APPEND);

			$message['ffmpeg'][$ccount]['file'] = $mfile;
			$message['ffmpeg'][$ccount]['execout'] = $execout;
			$message['ffmpeg'][$ccount]['execlog'] = $execlog;
			$message['ffmpeg'][$ccount]['fileduration'] = $fileDuration;
			$message['ffmpeg'][$ccount]['fduration'] = $fduration;
			$message['ffmpeg'][$ccount]['concatdur'] = $concatdur;
			$ccount++;
		}

		// $message['totaltime'] = floor(array_sum($concatdur));
		$message['totaltime'] = array_sum($concatdur);

		if ($vsource == 'cagiva01' or $vsource == 'cagiva02') {
			$message['execjoin'] = $ffmpegpath." -f concat -safe 0 -i ".$concatfile." -c:v libx264 -preset ultrafast -crf 26 -vf yadif -y ".$destdir.$message['joinfilename'];
		} else {
			$message['execjoin'] = $ffmpegpath." -f concat -safe 0 -i ".$concatfile." -c copy -y ".$destdir.$message['joinfilename'];
		}

		putenv("FFREPORT=file=".$this->joinLogDir().$now.".log:level=32");
		exec("/bin/bash -c \"".$message['execjoin']." 1> /dev/null 2>/dev/null &\"", $message['execlog'], $message['execreturn']);

		header('Access-Control-Allow-Origin: *');
		header('Content-Type: application/json');
		print json_encode($message);
	}

	public function getjoinvideo($file) {
		$destfile = $this->joinDir().$file;

		if (file_exists($destfile)) {
			header('Access-Control-Allow-Origin: *');
			header('X-Sendfile: ' . $destfile);
			header('Content-Type: video/mp4');
			header('Content-Disposition: attachment; filename="'.$filename.'"');
		} else {
			header("HTTP/1.1 404 Not Found");
		}
	}

	public function cropjoinvideos($file, $start, $dur) {
		$ffmpegpath = "/usr/bin/ffmpeg";
		$nfile = str_replace(".mp4", "", $file);
		$src = explode("_", $file);
		$source = $src[0];
		$destfile = $this->joinDir().$file;
		$start = str_replace("-", ":", $start);
		$dur = str_replace("-", ":", $dur);
		$now = strtotime("now");
		$message["id"] = $now;
		$message['cropfilename'] = $nfile."_".$now."_crop.mp4";

		if ($source == 'cagiva01' or $source == 'cagiva02') {
			$message['execcrop'] = $ffmpegpath." -ss ".$start." -i ".$destfile." -t ".$dur." -c:v libx264 -preset ultrafast -crf 26 -vf yadif -y ".$this->cropDir().$message['cropfilename'];
		} else if ($source == 'vespa' or $source == 'honda01' or $source == 'honda02' or $source == 'buell04' or $source == 'buell05') {
			$message['execcrop'] = $ffmpegpath." -ss ".$start." -i ".$destfile." -t ".$dur." -preset veryfast -r 30 -vf yadif -y ".$this->cropDir().$message['cropfilename'];
		} else {
			$message['execcrop'] = $ffmpegpath." -ss ".$start." -i ".$destfile." -t ".$dur." -preset ultrafast -y ".$this->cropDir().$message['cropfilename'];
		}

		putenv("FFREPORT=file=".$this->cropDir().$now.".log:level=32");
		exec("/bin/bash -c \"".$message['execcrop']." 1> /dev/null 2>/dev/null &\"", $message['execlog'], $message['execreturn']);

		header('Access-Control-Allow-Origin: *');
		header('Content-Type: application/json');
		print json_encode($message);
	}

	public function cropprogress($pid, $croptime) {
		$content = @file_get_contents($this->cropLogDir().$pid.'.log');

		if ($content) {
			//get duration of source
			// preg_match("/Duration: (.*?), start:/", $content, $matches);

			// $rawDuration = $matches[1];
			$rawDuration = $croptime;

			//rawDuration is in 00:00:00.00 format. This converts it to seconds.
			$ar = array_reverse(explode(":", $rawDuration));
			$duration = floatval($ar[0]);
			if (!empty($ar[1])) $duration += intval($ar[1]) * 60;
			if (!empty($ar[2])) $duration += intval($ar[2]) * 60 * 60;

			//get the time in the file that is already encoded
			preg_match_all("/time=(.*?) bitrate/", $content, $matches);

			$rawTime = array_pop($matches);

			//this is needed if there is more than one match
			if (is_array($rawTime)){$rawTime = array_pop($rawTime);}

			//rawTime is in 00:00:00.00 format. This converts it to seconds.
			$ar = array_reverse(explode(":", $rawTime));
			$time = floatval($ar[0]);
			if (!empty($ar[1])) $time += intval($ar[1]) * 60;
			if (!empty($ar[2])) $time += intval($ar[2]) * 60 * 60;

			//calculate the progress
			// $percent = round(($time/$duration) * 100);
			$percent = floor(($time/$duration) * 100);

			// echo "Duration: " . $duration . "<br>";
			// echo "Current Time: " . $time . "<br>";
			// echo "Progress: " . $percent . "%";

			$progress["duration"] = $duration;
			$progress["currenttime"] = $time;
			$progress["percent"] = $percent;

			header('Access-Control-Allow-Origin: *');
			header('Content-Type: application/json');
			print json_encode($progress);
		}
	}

	public function joinprogress($pid, $totaltime) {
		$content = @file_get_contents($this->joinLogDir().$pid.'.log');

		if ($content) {
			//get duration of source
			preg_match("/Duration: (.*?), start:/", $content, $matches);

			// $rawDuration = $matches[1];
			$rawDuration = $totaltime;

			//rawDuration is in 00:00:00.00 format. This converts it to seconds.
			$ar = array_reverse(explode(":", $rawDuration));
			$duration = floatval($ar[0]);
			if (!empty($ar[1])) $duration += intval($ar[1]) * 60;
			if (!empty($ar[2])) $duration += intval($ar[2]) * 60 * 60;

			//get the time in the file that is already encoded
			preg_match_all("/time=(.*?) bitrate/", $content, $matches);

			$rawTime = array_pop($matches);

			//this is needed if there is more than one match
			if (is_array($rawTime)){$rawTime = array_pop($rawTime);}

			//rawTime is in 00:00:00.00 format. This converts it to seconds.
			$ar = array_reverse(explode(":", $rawTime));
			$time = floatval($ar[0]);
			if (!empty($ar[1])) $time += intval($ar[1]) * 60;
			if (!empty($ar[2])) $time += intval($ar[2]) * 60 * 60;

			//calculate the progress
			// $percent = round(($time/$duration) * 100);
			$percent = floor(($time/$duration) * 100);

			// echo "Duration: " . $duration . "<br>";
			// echo "Current Time: " . $time . "<br>";
			// echo "Progress: " . $percent . "%";

			$progress["duration"] = $duration;
			$progress["currenttime"] = $time;
			$progress["percent"] = $percent;

			header('Access-Control-Allow-Origin: *');
			header('Content-Type: application/json');
			print json_encode($progress);
		}
	}

	public function encodeprogress($file) {
		$content = @file_get_contents('/tv/LOG/ENCODE/test.log');

		if ($content) {
			//get duration of source
			// preg_match("/Duration: (.*?), start:/", $content, $matches);

			// $rawDuration = $matches[1];
			$rawDuration = $croptime;

			//rawDuration is in 00:00:00.00 format. This converts it to seconds.
			$ar = array_reverse(explode(":", $rawDuration));
			$duration = floatval($ar[0]);
			if (!empty($ar[1])) $duration += intval($ar[1]) * 60;
			if (!empty($ar[2])) $duration += intval($ar[2]) * 60 * 60;

			//get the time in the file that is already encoded
			preg_match_all("/time=(.*?) bitrate/", $content, $matches);

			$rawTime = array_pop($matches);

			//this is needed if there is more than one match
			if (is_array($rawTime)){$rawTime = array_pop($rawTime);}

			//rawTime is in 00:00:00.00 format. This converts it to seconds.
			$ar = array_reverse(explode(":", $rawTime));
			$time = floatval($ar[0]);
			if (!empty($ar[1])) $time += intval($ar[1]) * 60;
			if (!empty($ar[2])) $time += intval($ar[2]) * 60 * 60;

			//calculate the progress
			$percent = round(($time/$duration) * 100);

			// echo "Duration: " . $duration . "<br>";
			// echo "Current Time: " . $time . "<br>";
			// echo "Progress: " . $percent . "%";

			$progress["duration"] = $duration;
			$progress["currenttime"] = $time;
			$progress["percent"] = $percent;

			header('Access-Control-Allow-Origin: *');
			header('Content-Type: application/json');
			print json_encode($progress);
		}
	}

	public function detectsilence() {
		// $datesp = explode("-", $date);
		// $year = $datesp[0];
		// $month = $datesp[1];
		// $dateObj = DateTime::createFromFormat('!m', $month);
		// $monthName = $dateObj->format('F');
		// $day = $datesp[2];

		// $rootdirs = $this->rootdir();
		// foreach ($rootdirs as $dir) {
		// 	$chdir = $dir."/".$month."-".$monthName."/".$date."/";
		// 	$sourcearr = explode("/", $dir);
		// 	$source = $sourcearr[2];
		// 	$func = function($var) {
		// 			$charr = explode("/", $var);
		// 			$channel = $charr[7];
		// 			return $channel;
		// 	};
		// 	$channels[$source] = array_map($func, glob($chdir."*", GLOB_ONLYDIR));
		// }

		// $fcount = 0;
		// foreach ($channels as $csource => $source) {
		// 	$chsource = $csource;
		// 	foreach ($source as $cchannel ) {
		// 		$cchannelarr = explode("_", $cchannel);
		// 		$rootdir = $this->srcdir($chsource);
		// 		$destdir = $rootdir."/".$month."-".$monthName."/".$date."/".$cchannel."/";
		// 		$filesindir = array_map('basename', glob($destdir.'*.{mp4,MP4}', GLOB_BRACE));
		// 		foreach ($filesindir as $file) {
		// 			$fcount++;
		// 			$filearr = explode("_", $file);
		// 			$fdate = $filearr[0];
		// 			$ftime = str_replace("-", ":", $filearr[1]);
		// 			$filetimestamp = strtotime($fdate." ".$ftime);
		// 			if ($fcount == 10) {
		// 				exit();
		// 			}
		// 			echo $file." timestamp: ".$filetimestamp;
		// 			echo "<br>";
		// 		}
		// 	}
		// }

		$verify = exec("/tv/scripts/detect_silence_json.sh", $verifyoutput);

		header('Access-Control-Allow-Origin: *');
		header('Content-Type: application/json');
		print json_encode($verifyoutput, JSON_PRETTY_PRINT);
	}

	public function getvinhetas() {
		$destdir = "/mnt/bmw/VIDEOS/VINHETAS/";
		$vifiles = array_map('basename', glob($destdir.'*.{mp4,MP4}', GLOB_BRACE));

		header('Access-Control-Allow-Origin: *');
		header('Content-Type: application/json');
		print json_encode($vifiles);
	}

	public function createzip() {
		// $postdata = ($_POST = json_decode(file_get_contents("php://input"),true));

		$postdata['source'] = $this->input->post("source");
		$postdata['files'] = $this->input->post("files");

		$arrcount = count($postdata['files']) - 1;

		$firstfile = $postdata['files'][0];
		$firstfilename = str_replace(".mp4", "", $firstfile);
		$ffilenarr = explode("_", $firstfilename);
		$fdate = $ffilenarr[0];
		$ftime = $ffilenarr[1];
		$fchannel = $ffilenarr[2];
		$fstate = $ffilenarr[3];

		$lastfile = $postdata['files'][$arrcount];
		$lastfilename = str_replace(".mp4", "", $lastfile);
		$lfilenarr = explode("_", $lastfilename);
		$ldate = $lfilenarr[0];
		$ltime = $lfilenarr[1];

		$zipfilename = $fchannel."_".$fstate."_".$fdate."_".$ftime."_a_".$ldate."_".$ltime.".zip";

		$zip = new ZipArchive();
		$ret = $zip->open('/tv/ZIP/'.$zipfilename, ZipArchive::CREATE | ZipArchive::OVERWRITE);
		if ($ret !== TRUE) {
			printf('Failed with code %d', $ret);
		} else {
			$thumbrootdir = $this->thumbsrcdir($postdata['source']);
			foreach ($postdata['files'] as $file) {
				$file = str_replace(".mp4", "", $file);
				$filenarr = explode("_", $file);
				$date = $filenarr[0];
				$datearr = explode("-", $filenarr[0]);
				$year = $datearr[0];
				$month = $datearr[1];
				$dateObj = DateTime::createFromFormat('!m', $month);
				$monthName = $dateObj->format('F');
				$day = $datearr[2];

				$time = $filenarr[1];
				$channel = $filenarr[2];
				$state = $filenarr[3];

				$path = $thumbrootdir."/".$month."-".$monthName."/".$date."/".$channel."_".$state."/".$file."/";
				$options = array('add_path' => '/', 'remove_all_path' => TRUE);
				$zip->addGlob($path.'*.jpg', GLOB_BRACE, $options);
			}
			$zip->close();

			sleep(3);

			$response['zipfile'] = $zipfilename;

			header('Access-Control-Allow-Origin: *');
			header('Content-Type: application/json');
			print json_encode($response);
		}
	}

	public function downloadzip($zipfile) {
		$zipfilepath = '/tv/ZIP/'.$zipfile;

		if (file_exists($zipfilepath)) {
			header('Access-Control-Allow-Origin: *');
			header('X-Sendfile: '.$zipfilepath);
			header('Content-Type: application/octet-stream');
		} else {
			header("HTTP/1.1 404 Not Found");
		}
	}

	public function getstopchannels() {
		$verifyoutput = file_get_contents('/tv/scripts/verify_channels.json');

		header('Access-Control-Allow-Origin: *');
		header('Content-Type: application/json');
		print $verifyoutput;
	}

	public function getchannelsinfo() {
		$verifyoutput = file_get_contents('/tv/scripts/CARDs.json');

		header('Access-Control-Allow-Origin: *');
		header('Content-Type: application/json');
		print $verifyoutput;
	}

	public function downfile() {
		echo 'teste';
	}
}
