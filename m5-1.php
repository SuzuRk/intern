<!--完了 1/4(火)-->
<!DOCTYPE html>
<html lang="ja"></html>
<?php
    $dsn = "************************"; //DSN：Data Source Name
    $user = "**********";
    $password = "************";
    //PDO：PHPとデータベースの接続(PHP Data Objects)/接続オプション＝エラーモード(デバッグ用)
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    
    $sql = "CREATE TABLE IF NOT EXISTS bulletin_board"
        ." ("
        . "id INT AUTO_INCREMENT PRIMARY KEY,"
        //AUTO_INCREMENT：自動登録ナンバリング。データは整数型。カラムに値が指定されなかった場合、自動的に値を割り当て、その値は1ずつ増加し連番になる。
        //PRIMARY KEY：テーブル内でレコードを一意に識別することができるように指定される列(カラム)のこと。この設定によって、重複する値やNULLは格納できなくなる。
        . "name char(32)," 
        //nameの文字列を32文字で固定。足りない分は空白で埋める。文字列末尾の空白はデータ取得時には削除される。 char：固定長文字列
        . "comment TEXT," //TEXT：可変長文字列
        . "postdate datetime,"
        . "password char(20)"//要素の最後に,を入れてはいけない(エラーが出る)
        .");";
        $stmt = $pdo->query($sql);
        //stmt：PDO Statement, 実行後にSQLの実行結果に関する情報を得る
        
        //$sql ='SHOW TABLES';
        //$result = $pdo -> query($sql);
        //foreach ($result as $row){
        //    echo $row[0];
        //    echo '<br>';
        //}
        //echo "<hr>";
        
    //投稿フォーム
    if(!empty($_POST["name"]) && !empty($_POST["comment"]) && !empty($_POST["password"])){
        $name = $_POST["name"];
        $comment = $_POST["comment"];
        $password = $_POST["password"];
        
        date_default_timezone_set('Asia/Tokyo');
        $postdate = date("Y/m/d H:i:s");
        
        //投稿フォーム→新規投稿
        if(empty($_POST["edit_flag"])){
            $sql = $pdo -> prepare("INSERT INTO bulletin_board (name, comment, postdate, password) VALUES (:name, :comment, :postdate, :password)");
            $sql -> bindParam(':name', $name, PDO::PARAM_STR);
            $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
            $sql -> bindParam(':postdate', $postdate, PDO::PARAM_STR);
            $sql -> bindParam(':password', $password, PDO::PARAM_STR);
            //$name = '（好きな名前）';
            //$comment = '（好きなコメント）'; //好きな名前、好きな言葉は自分で決めること
            $sql -> execute();

        //投稿フォーム→編集
        }elseif(!empty($_POST["edit_flag"])){
            $edit_num = $_POST["edit_num"]; //変更する投稿番号
            $edit_pass = $_POST["edit_pass"];
            $id = $edit_num;
            
            if(!empty($id)){
                $sql = 'SELECT * FROM bulletin_board WHERE id=:id';
                $stmt = $pdo -> query($sql);
                $results = $stmt -> fetchAll();
                foreach ($results as $row){//$rowの中にはテーブルのカラム名が入る
                    $exist_pass = $row["password"];
                    if($exist_pass == $edit_pass){
                        $sql = 'UPDATE bulletin_board SET name=:name,comment=:comment WHERE id=:id';
                        $stmt = $pdo -> prepare($sql);
                        $stmt -> bindParam(':name', $name, PDO::PARAM_STR);
                        $stmt -> bindParam(':comment', $comment, PDO::PARAM_STR);
                        $stmt -> bindParam(':password', $password, PDO::PARAM_STR);
                        $stmt -> bindParam(':id', $id, PDO::PARAM_INT);
                        $stmt -> execute();
                    }
                }
            }else{
                //echo "id is not set.";
            }
        }
    
    //削除フォーム
    }elseif(!empty($_POST["delete_num"]) && !empty($_POST["delete_pass"])){
        $delete_num = $_POST["delete_num"];
        $delete_pass = $_POST["delete_pass"];
        $id = $delete_num;
        
        if(!empty($id)){
            $sql = 'SELECT * FROM bulletin_board';
            $stmt = $pdo -> query($sql);
            $results = $stmt -> fetchAll();
            foreach ($results as $row){//$rowの中にはテーブルのカラム名が入る
                $exist_pass = $row["password"];
                if($exist_pass == $delete_pass){
                    $sql = 'delete from bulletin_board WHERE id=:id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmt->execute();
                }
            }
        }else{
            //echo "delete_id is not set.";
        }
        
        
    //編集フォーム
    }elseif(!empty($_POST["edit_num"]) && !empty($_POST["edit_pass"])){
        $edit_num = $_POST["edit_num"];
        $edit_pass = $_POST["edit_pass"];
        $id = $edit_num;
        
        if(!empty($id)){
            $sql = 'SELECT * FROM bulletin_board';
            $stmt = $pdo -> query($sql);
            $results = $stmt -> fetchAll();
            foreach ($results as $row){//$rowの中にはテーブルのカラム名が入る
                $exist_pass = $row["password"];
                if($exist_pass == $edit_pass){
                    $edit_number = $row['id'];
                    $edit_name = $row['name'];
                    $edit_comment = $row['comment'];
                }
            }
        }else{
            //echo "edit_id is not set.";
        }
    }else{
        //echo "error.";
    }
?>

        <p>
            掲示板だよ
        <form action="" method="post" name="post_form">
            投稿フォーム<br>
            <input type="text" name="name" placeholder="名前" value=<?php if(!empty($edit_name)){echo $edit_name;} ?>><br>
            <input type="text" name="comment" placeholder="コメント" value=<?php if(!empty($edit_comment)){echo $edit_comment;} ?>><br>
            <input type="password" name="password" placeholder="パスワード" value=<?php if(!empty($edit_pass)){echo $edit_pass;} ?>>
            <input type="submit" name="submit" value="投稿"><br>
            <input type="hidden" name="edit_flag" value=<?php if(!empty($edit_num)){echo $edit_num;} ?>>
        </form>
        </p>
        
        <p>
        <form action="" method="post" name="delete_form">
            削除フォーム<br>
            <input type="number" name="delete_num" placeholder="削除対象投稿番号"><br>
            <input type="password" name="delete_pass" placeholder="パスワード">
            <input type="submit" name="delete" value="削除">
        </form>
        </p>
        
        <p>
        <form action="" method="post" name="edit_form">
            編集フォーム<br>
            <input type="number" name="edit_num" placeholder="編集対象投稿番号"><br>
            <input type="password" name="edit_pass" placeholder="パスワード">
            <input type="submit" name="edit" value="編集">
        </form>
        </p>
        
<?php
    $dsn = "mysql:dbname=tb230858db;host=localhost"; //DSN：Data Source Name
    $user = "tb-230858";
    $password = "dx7ZEkQwVu";
    //PDO：PHPとデータベースの接続(PHP Data Objects)/接続オプション＝エラーモード(デバッグ用)
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    
    $sql = 'SELECT * FROM bulletin_board';
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll();
    foreach ($results as $row){
        //$rowの中にはテーブルのカラム名が入る
        echo $row['id'].' ';
        echo $row['name'].' ';
        echo $row['comment'].' ';
        echo $row['postdate'].'<br>';
        //echo "<hr>";
    }

?>
</html>
