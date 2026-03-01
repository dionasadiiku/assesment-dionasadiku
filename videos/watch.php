<?php
require_once '../config/config.php';
require_once '../middleware/AuthMiddleware.php';
require_once '../models/video.php';
require_once '../models/bookmark.php';
require_once '../models/annotation.php';

AuthMiddleware::handle();

$videoModel = new Video($pdo);
$bookmarkModel = new Bookmark($pdo);
$annotationModel = new Annotation($pdo);

if (!isset($_GET['id'])) die("Video not found.");

$video = $videoModel->findById($_GET['id']);
if (!$video) die("Video does not exist.");


if (isset($_GET['delete_bookmark'])) {
    $id = (int) $_GET['delete_bookmark'];
    $bookmarkModel->delete($id, $_SESSION['user_id']);
    header("Location: watch.php?id=" . $video['id']);
    exit();
}
if (isset($_GET['delete_annotation'])) {
    $id = (int) $_GET['delete_annotation'];
    $annotationModel->delete($id, $_SESSION['user_id']);
    header("Location: watch.php?id=" . $video['id']);
    exit();
}


if (isset($_POST['add_bookmark'])) {
    $timestamp = (int) ($_POST['timestamp'] ?? -1);
    $title = trim($_POST['title'] ?? '');

    if ($timestamp >= 0 && !empty($title)) {
        $bookmarkModel->create($video['id'], $_SESSION['user_id'], $timestamp, $title);
    }

    header("Location: watch.php?id=" . $video['id']);
    exit();
}


if (isset($_POST['add_annotation'])) {
    $timestamp = (int) ($_POST['timestamp'] ?? -1);
    $description = trim($_POST['description'] ?? '');
    $drawing = $_POST['drawing'] ?? null;

    
    if ($timestamp >= 0 && !empty($description)) {
        $annotationModel->create($video['id'], $_SESSION['user_id'], $timestamp, $description, $drawing);
    }

    header("Location: watch.php?id=" . $video['id']);
    exit();
}


$bookmarks = $bookmarkModel->getByVideo($video['id']);
$annotations = $annotationModel->getByVideo($video['id']);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($video['title']) ?></title>
<link rel="stylesheet" href="../css/watch.css">
<style>
.trash-icon { cursor:pointer; color:#ef4444; margin-left:8px; opacity:0.85; transition:opacity 0.15s; }
.trash-icon:hover { opacity:1; }
.annotation-overlay { display:block; pointer-events:none; position:absolute; top:10px; left:10px; background:rgba(0,0,0,0.6); color:white; padding:4px 8px; border-radius:4px; font-size:14px; }
.video-wrapper { position:relative; display:inline-block; }
#annotationCanvas { position:absolute; top:0; left:0; z-index:2; }
#annotationOverlay { z-index:3; }
</style>
</head>
<body>

<div class="container">
  <div class="main-grid">

    
    <div class="video-card">
      <div class="video-title"><?= htmlspecialchars($video['title']) ?></div>
      <div class="video-description"><?= htmlspecialchars($video['description']) ?></div>

      <div class="video-wrapper">
        <video id="videoPlayer" controls class="video-element">
          <source src="../uploads/<?= htmlspecialchars($video['filename']) ?>" type="video/mp4">
        </video>

        <canvas id="annotationCanvas"></canvas>
        <div id="annotationOverlay" class="annotation-overlay"></div>
      </div>
    </div>


    <div class="side-panel">


<div class="panel-card">
  <h3>🔖 Bookmarks</h3>
  

  <form method="POST">
    <input type="hidden" name="timestamp" id="bookmarkTimestamp">
    <input type="text" name="title" placeholder="Bookmark title..." required>
    <button type="submit" name="add_bookmark" onclick="setBookmarkTime()">Add</button>
  </form>

  <hr style="opacity:0.12; margin:12px 0;">


  <h4>Saved Bookmarks</h4>
  <div class="saved-list">
    <?php if (empty($bookmarks)): ?>
      <p style="color:#94a3b8;">No bookmarks yet.</p>
    <?php else: ?>
      <ul>
        <?php foreach ($bookmarks as $b): ?>
        <li class="list-item">
          <a href="#" onclick="goToTime(<?= (int)$b['timestamp_seconds'] ?>)">
            <?= htmlspecialchars($b['title']) ?>
          </a>
          <span class="badge"><?= (int)$b['timestamp_seconds'] ?>s</span>
          <span class="trash-icon" onclick="confirmDelete('bookmark', <?= (int)$b['id'] ?>)">🗑️</span>
        </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>

      <div class="panel-card">
        <h3>📝 Annotations</h3>

        <form id="annotationForm" method="POST" onsubmit="return saveAnnotation(event)">
          <input type="hidden" name="timestamp" id="annotationTimestamp">
          <input type="hidden" name="drawing" id="drawingData">
          <input type="text" name="description" id="annotationDescription" placeholder="Description" required>

          <div style="display:flex;gap:8px; margin-top:8px;">
            <button type="button" id="startDrawBtn" onclick="enableDrawing()">Start Drawing</button>
            <button type="button" id="clearBtn" onclick="clearCanvas()">Clear</button>
            <button type="submit" name="add_annotation">Save Annotation</button>
          </div>
        </form>

        <hr style="opacity:0.12; margin:12px 0;">

        <h4>Saved Annotations</h4>
        <?php if (empty($annotations)): ?>
          <p style="color:#94a3b8;">No annotations yet.</p>
        <?php else: ?>
          <ul>
            <?php foreach ($annotations as $a): ?>
              <li class="list-item">
                <a href="#" onclick="goToTime(<?= (int)$a['timestamp_seconds'] ?>)">
                  <?= htmlspecialchars($a['description']) ?>
                </a>
                <span class="badge"><?= (int)$a['timestamp_seconds'] ?>s</span>
                <span class="trash-icon" onclick="confirmDelete('annotation', <?= (int)$a['id'] ?>)">🗑️</span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

<script>
const video = document.getElementById('videoPlayer');
const canvas = document.getElementById('annotationCanvas');
const ctx = canvas.getContext('2d');
const overlay = document.getElementById('annotationOverlay');
const startBtn = document.getElementById('startDrawBtn');
const drawingField = document.getElementById('drawingData');
const tsInput = document.getElementById('annotationTimestamp');

let drawingEnabled=false, isDrawing=false, lastShownSecond=-1, overlayTimeout=null;
const annotations = <?= json_encode($annotations, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT) ?> || [];

function resizeCanvas(){
  const rect = video.getBoundingClientRect();
  const dpr = window.devicePixelRatio||1;
  canvas.style.width = rect.width+'px';
  canvas.style.height = rect.height+'px';
  canvas.width = Math.max(1, Math.floor(rect.width*dpr));
  canvas.height = Math.max(1, Math.floor(rect.height*dpr));
  ctx.setTransform(dpr,0,0,dpr,0,0);
}
video.addEventListener('loadedmetadata', resizeCanvas);
window.addEventListener('resize', resizeCanvas);
resizeCanvas();
canvas.style.pointerEvents='none';

function enableDrawing(){
  if(!video.paused){ alert('Pause video first'); return; }
  drawingEnabled=true; canvas.style.pointerEvents='auto'; startBtn.disabled=true;
}
video.addEventListener('play', ()=>{ drawingEnabled=false; canvas.style.pointerEvents='none'; startBtn.disabled=false; });

function getPointerPos(e){
  const rect=canvas.getBoundingClientRect();
  return e.touches?{x:e.touches[0].clientX-rect.left,y:e.touches[0].clientY-rect.top}:{x:e.clientX-rect.left,y:e.clientY-rect.top};
}
canvas.addEventListener('mousedown', e=>{ if(!drawingEnabled) return; isDrawing=true; const p=getPointerPos(e); ctx.beginPath(); ctx.moveTo(p.x,p.y); });
canvas.addEventListener('mousemove', e=>{ if(!isDrawing||!drawingEnabled) return; const p=getPointerPos(e); ctx.lineWidth=3; ctx.lineCap='round'; ctx.strokeStyle='red'; ctx.lineTo(p.x,p.y); ctx.stroke(); ctx.beginPath(); ctx.moveTo(p.x,p.y); });
canvas.addEventListener('mouseup', ()=>{ isDrawing=false; ctx.beginPath(); });
canvas.addEventListener('mouseleave', ()=>{ isDrawing=false; ctx.beginPath(); });
canvas.addEventListener('touchstart', e=>{ if(!drawingEnabled) return; isDrawing=true; const p=getPointerPos(e); ctx.beginPath(); ctx.moveTo(p.x,p.y); }, {passive:false});
canvas.addEventListener('touchmove', e=>{ if(!isDrawing||!drawingEnabled) return; e.preventDefault(); const p=getPointerPos(e); ctx.lineWidth=3; ctx.lineCap='round'; ctx.strokeStyle='red'; ctx.lineTo(p.x,p.y); ctx.stroke(); ctx.beginPath(); ctx.moveTo(p.x,p.y); }, {passive:false});
canvas.addEventListener('touchend', ()=>{ isDrawing=false; ctx.beginPath(); });

function clearCanvas(){ ctx.clearRect(0,0,canvas.width,canvas.height); }
function goToTime(sec){ video.currentTime=sec; video.play(); }
function setBookmarkTime(){ document.getElementById('bookmarkTimestamp').value=Math.floor(video.currentTime); }

function saveAnnotation(e){
  const ts=Math.floor(video.currentTime);
  tsInput.value=ts;
  const rect=video.getBoundingClientRect();
  const dpr=window.devicePixelRatio||1;
  const tmp=document.createElement('canvas'); tmp.width=Math.max(1, Math.floor(rect.width*dpr)); tmp.height=Math.max(1, Math.floor(rect.height*dpr));
  const tctx=tmp.getContext('2d'); tctx.setTransform(dpr,0,0,dpr,0,0);
  tctx.drawImage(canvas,0,0,rect.width,rect.height);
  drawingField.value=tmp.toDataURL('image/png');
  drawingEnabled=false; canvas.style.pointerEvents='none'; startBtn.disabled=false;
  return true;
}

video.addEventListener('timeupdate', ()=>{
  const sec=Math.floor(video.currentTime);
  if(sec===lastShownSecond) return;
  lastShownSecond=sec;
  const matched=annotations.filter(a=>parseInt(a.timestamp_seconds)===sec);
  if(matched.length===0){ overlay.classList.remove('show'); ctx.clearRect(0,0,canvas.width,canvas.height); return; }
  const ann=matched[0];
  if(ann.description && ann.description.trim()!==''){
    overlay.innerText=ann.description;
    overlay.classList.add('show');
    if(overlayTimeout) clearTimeout(overlayTimeout);
    overlayTimeout=setTimeout(()=>overlay.classList.remove('show'),3000);
  } else overlay.classList.remove('show');

  if(ann.drawing){ const img=new Image(); img.src=ann.drawing; img.onload=function(){ resizeCanvas(); ctx.clearRect(0,0,canvas.width,canvas.height); const rect=video.getBoundingClientRect(); ctx.drawImage(img,0,0,rect.width,rect.height); }; } else ctx.clearRect(0,0,canvas.width,canvas.height);
});

function confirmDelete(type,id){ if(confirm('Are you sure you want to delete this '+type+'?')) window.location='watch.php?id=<?= $video['id'] ?>&delete_'+type+'='+id; }
</script>
</body>
</html>