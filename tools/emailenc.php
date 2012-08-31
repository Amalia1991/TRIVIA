<?php
/*
 * Encoder for defending  e-mail links from spam-bots
 * by Ennin (ennin[at]mail[dot]ru)
 * based on Blackman's E-mail Encoder (see http://blackman2003.narod.ru/articles/spamatt/index.html) algorithms
 *
 * encodeJS is algorithm that just prints parts of email link by using JScript document.write;
 * encodeJSP is paranoia. Based on event processors onClick, onMouseLeave, onMouseOver
 *
 * syntax:
 * encodeJS($email[,$name[,$subject[,$linkattributes]]]);
 * encodeJSP($email[,$name[,$subject[,$linkattributes[,$false_address]]]]);
 *
 * examples for encodeJS:
 *  encodeJS('user@domain.zone','send a letter to user@domain.zone');
 *  encodeJS('user@domain.zone','send a letter','a letter from testing email encoder','class=maillink');
 * examples for encodeJSP:
 *  encodeJSP('user@domain.zone','send a letter to user@domain.zone');
 *  encodeJSP('user@domain.zone','send a letter','a letter from testing email encoder','style=text-decoration:none;','spam@microsoft.com');
 *
 * functions return strings with codes. So you must use echo function(); or $var=function();echo $var;
 *
 */

#-------------------------------------------------------------------------------------
# НИЖЕСЛЕДУЮЩИЕ СТРОКИ НАСТОЯТЕЛЬНО НЕ РЕКОМЕНДУЕТСЯ ИЗМЕНЯТЬ !
#
# ПРОГРАММА РАСПРОСТРАНЯЕТСЯ ПО ПРИНЦИПУ "КАК ЕСТЬ".
# ПРИ ЭТОМ НЕ ПРЕДУСМАТРИВАЕТСЯ НИКАКИХ ГАРАНТИЙ, ЯВНЫХ ИЛИ ПОДРАЗУМЕВАЕМЫХ.
# ВЫ ИСПОЛЬЗУЕТЕ ЕЕ НА СВОЙ СОБСТВЕННЫЙ РИСК. АВТОР НЕ ОТВЕЧАЕТ ЗА ПОТЕРИ ДАННЫХ, ПОВРЕЖДЕНИЯ,
# ПОТЕРИ ПРИБЫЛИ ИЛИ ЛЮБЫЕ ДРУГИЕ ВИДЫ ПОТЕРЬ, СВЯЗАННЫЕ С ИСПОЛЬЗОВАНИЕМ
# (ПРАВИЛЬНЫМ ИЛИ НЕПРАВИЛЬНЫМ) ЭТОЙ ПРОГРАММЫ.
#-------------------------------------------------------------------------------------

function replaceSymbol($str,$smb)
    {
        switch($smb)
        {
            case("\""):$retstr=str_replace("\"","&quot;",$str);break;
            case("&"):$retstr=str_replace("&","&amp;",   $str);break;
            case("<"):$retstr=str_replace("<","&lt;",    $str);break;
            case(">"):$retstr=str_replace(">","&gt;",    $str);break;
            case("©"):$retstr=str_replace("©","&copy;",  $str);break;
            default:$retstr=str_replace($smb,"&#".ord($smb).";",$str);break;
        }
        return $retstr;
    }

function smokeSymbol($str,$smb)
    {
        switch($smb)
        {
            case("."):$retstr=mt_rand(0,1)?str_replace(".","&middot;",$str):str_replace(".","&cedil;",$str);break;
            case("@"):$retstr=mt_rand(0,1)?str_replace("@","&copy;",$str):str_replace("@","&reg;",$str);break;
        }
        return $retstr;
    }

function randomCut($mls)
    {
        $parts=range(0,1);
        $dif=mt_rand(0,strlen($mls)-1)+1;
        if($dif==strlen($mls))
            $dif--;
        $part0=substr($mls,0,$dif);
        $part1=substr($mls,$dif,strlen($mls)-$dif+1);
        return array($part0,$part1);
    }

function generateJSName($minl,$maxl,$letonly)
    {
        $tt=chr(mt_rand(0,25)+97);
        $m=mt_rand($minl,$maxl);
        for($n=0;$n<$m;$n++)
            $tt.=($letonly?0:mt_rand(0,1))?chr(mt_rand(0,9)+48):chr(mt_rand(0,25)+97);
        return $tt;
    }

function encodeJS($email,$name="",$subj="",$linkattr="")
    {
        if(!$email)
            {
                echo "PHP error";
                return;
            }
        if(!$name)
            $name=$email;
        $vars=range(0,9);
        for($i=0;$i<10;$i++)
            $vars[$i]=generateJSName(4,8,0);
        $result="";
        $result.="<script language=JavaScript><!--\r\n";
        $parts=explode('@',$email);
        $parts[1]=replaceSymbol($parts[1],'.');
        $result.="var $vars[0]=\"hre\"; var $vars[1]=\"f='mai\"; var $vars[2]=\"lto:\";";
        $result.="var $vars[3]=\"$parts[0]\"; var $vars[4]=\"$parts[1]\";";
        if($email!=$name)
            {
                if($name)
                    {
                        if(strpos($name,'@'))
                            {
                                $pname=explode('@',$name);
                                $pname[1]=replaceSymbol($pname[1],'.');
                                $result.="var $vars[5]='$pname[0]'; var $vars[6]='$pname[1]';";
                            }
                        else
                            $result.="var $vars[5]=\"$name\";";
                    }
                else
                    $result.="var $vars[5]=\"".smokeSymbol(smokeSymbol($email,'@'),'.')."\";";
            }
        $result.="var $vars[7]=' '+$vars[0]+$vars[1]+$vars[2]; var $vars[8]=$vars[3]+'@'+$vars[4];";
        if($email!=$name)
            $result.="var $vars[9]=$vars[5]".(strpos($name,'@')?"+\"@\"+$vars[6]":"").";";
        $result.="document.write('<a'+$vars[7]+$vars[8]";
        if($subj)
            $result.="+'?subject=\"$subj\"'";
        $result.="+\"'\"";
        if($linkattr)
            $result.="+' $linkattr'";
        $result.="+'>'+";
        if($email!=$name)
            $result.=$vars[9];
        else
            $result.=$vars[8];
        $result.="+\"</a>\");\r\n";
        $result.="//--></script>";
        $result.="<noscript>";
        if(!strpos($name,'@'))
            $result.=$name;
        else
            $result.=smokeSymbol(smokeSymbol($name,'@'),'.');
        $result.="</noscript>";

        return $result;
    }

function encodeJSP($email,$name="",$subj="",$linkattr="",$referrer="")
    {
        $result='';
        if(!$email)
            {
                echo "PHP error";
                return;
            }
        if(!$name)
            $name=$email;
        $vars=range(0,9);
        for($i=0;$i<10;$i++)
            $vars[$i]=generateJSName(4,8,0);
        $result.="<script type='text/javascript' language='javascript'><!--\r\n";
        $result.="function $vars[0](){var $vars[9]=\"@\";";
        $parts=explode('@',$email);
        $pn0=randomCut($parts[0]);
        $pn1=randomCut($parts[1]);
        $result.="$vars[9]+=\"".$pn1[0]."\"; ";
        $result.="$vars[9]=\"".$pn0[1]."\"+$vars[9];";
        $result.="$vars[9]+=\"".$pn1[1]."\"; ";
        $result.="$vars[9]=\"".$pn0[0]."\"+$vars[9];";
        $result.="return $vars[9];};";
        if($email!=$name)
            {
                $result.="function $vars[1](){";
                if($name)
                    {
                        if(strpos($name,'@'))
                            {
                                $pname=explode('@',$name);
                                $dif=mt_rand(0,strlen($pname[1])-1)+1;
                                if($dif==strlen($pname[1]))$dif--;
                                if($dif==0)$dif++;
                                $result.="var $vars[7]=\"".substr($pname[1],$dif,strlen($pname[1])-$dif+1)."\"; ";
                                $result.="$vars[7]=\"".substr($pname[1],0,$dif)."\"+$vars[7]; ";
                                $st=$pname[0];
                            }
                        else
                            $st=$name;
                        $dif=mt_rand(0,strlen($st)-1)+1;
                        if($dif==strlen($st))$dif--;
                        if($dif==0)$dif++;
                        $result.="var $vars[8]=\"".substr($st,$dif,strlen($st)-$dif+1)."\";";
                        $result.="$vars[8]=\"".substr($st,0,$dif)."\"+$vars[8]; ";
                        if(strpos($name,'@'))
                            {
                                $result.="$vars[8]=$vars[8]+\"@\"+$vars[7];";
                            };
                    }
                else
                    $result.="$vars[8]=$vars[0]();";
                $result.="return $vars[8];};";
            };
        $result.="function $vars[2](){return \"".($subj?"?subject=\\\"$subj\\\"":"")."\";};";
        $result.="function $vars[3](){status=\"mailto:\"+$vars[0]()+$vars[2]();};";
        $result.="function $vars[4](){status=\"\";};";
        $result.="function $vars[5](){this.location=\"mailto:\"+$vars[0]()+$vars[2]();return false;};";
        $result.="document.write(\"<a href='mailto:".($referrer?$referrer:"support@microsoft.com")."' onMouseOver='$vars[3]();' onMouseLeave='$vars[4]();' onClick='return $vars[5]();'".($linkattr?" ".$linkattr:"").">\"+";
        $result.=(($email!=$name)?"$vars[1]":"$vars[0]")."()+\"</a>\");\r\n";
        $result.="//--></script>";
        $result.="<noscript>";
        if(!strpos($name,'@'))
            $result.=$name;
        else
            $result.=smokeSymbol(smokeSymbol($name,'@'),'.');
        $result.="</noscript>";
        return $result;
    };

#helps
/*
if(!$options)
    $options=$_GET['beeoptions'];
if(strpos($options,'help')===false);else
    {
        echo "<pre>";
        echo "<b>ABOUT</b>\n";
        echo "* Encoder for defending  e-mail links from spam-bots\n";
        echo "* by Ennin (ennin[at]mail[dot]ru)\n";
        echo "* based on <a href=http://blackman2003.narod.ru/articles/spamatt/index.html style=text-decoration:none>Blackman's E-mail Encoder</a> algorithms\n";
        echo "*\n";
        echo "* encodeJS is algorithm that just prints parts of email link by using JScript document.write;\n";
        echo "* encodeJSP is paranoia. Based on event processors onClick, onMouseLeave, onMouseOver\n";
        echo "*\n";
        echo "* syntax of using:\n";
        echo "* encodeJS(\$email[,\$name[,\$subject[,\$linkattributes]]]);\n";
        echo "* encodeJSP(\$email[,\$name[,\$subject[,\$linkattributes[,\$false_address]]]]);\n";
        echo "*\n";
        echo "* examples for encodeJS:\n";
        echo "*  encodeJS('user@domain.zone','send a letter to user@domain.zone');\n";
        echo "*  encodeJS('user@domain.zone','send a letter','a letter from testing email encoder','class=maillink');\n";
        echo "* examples for encodeJSP:\n";
        echo "*  encodeJSP('user@domain.zone','send a letter to user@domain.zone'); \n";
        echo "*  encodeJSP('user@domain.zone','send a letter','a letter from testing email encoder','style=text-decoration:none;','spam@microsoft.com');\n";
        echo "* functions return strings with codes. So you must use echo function() or \$var=function();echo \$var;";
        echo "</pre>";
    };
if(strpos($options,'example')===false);else
    {
        echo "<b>Examples</b>\n";
        echo "<table border=1 bordercolordark=0 cellspacing=0>";
        echo "<tr><td>method<td>code<td>result";
        echo "<tr><td>JS<td>echo encodeJS('user@domain.zone','send a letter to user@domain.zone');<td>";echo encodeJS('user@domain.zone','send a letter to user@domain.zone');
        echo "<tr><td>JS<td>echo encodeJS('user@domain.zone','send a letter','a letter from testing email encoder','class=maillink');<td>";echo encodeJS('user@domain.zone','send a letter','a letter from testing email encoder','class=maillink');
        echo "<tr><td>JSP<td>echo encodeJSP('user@domain.zone','send a letter to user@domain.zone');<td>";echo encodeJSP('user@domain.zone','send a letter to user@domain.zone');
        echo "<tr><td>JSP<td>echo encodeJSP('user@domain.zone','send a letter','a letter from testing email encoder','style=text-decoration:none;','spam@microsoft.com');<td>";echo encodeJSP('user@domain.zone','send a letter','a letter from testing email encoder','style=text-decoration:none;','spam@microsoft.com');
        echo "</table>";
    }
#/helps
*/