// pscore.c contest score function by GM @2025 V 2.0

const char *conid[]={"CQWWSSB","CQWWCW","CQWPXSSB","CQWPXCW","CQWWDIGI","4080","IARUHF","CQ160SSB","CQ160CW","SPDX","LZDX","OKOMSSB","OKOMCW","HADX","ARIDX","KOSSSB","KOSCW","RDAC","ARRLSSB","ARRLCW","RDXC","JIDXSSB","JIDXCW","YODX","CQM","WAESSB","WAECW","WAERTTY","CQ28","UBASSB","UBACW","IOTA","EUHF","ARISEZ"};
void conscore(MYSQL *con,char tok[][100],char *mycall){
  int contype,c,gg,vv,cqz[1000],ituz[1000],d,n;
  long l1,l2;
  char buf[1000],cont[1000][2],aux1[300],aux2[300],aux3[300],aux4[300],aux5[300],*p;
  MYSQL_RES *res;
  MYSQL_ROW row;
  double lat1,lat2,lon1,lon2;

  vv=sizeof(conid)/sizeof(conid[0]);
  for(contype=0;contype<vv;contype++)if(strncmp(tok[9],conid[contype],strlen(conid[contype]))==0)break;
  if(contype==vv)return;
  for(l1=0;l1<TOT3;l1++)for(l2=0;l2<TOTL2;l2++)ndata3[l1][l2]=0;
  row=searchcty(con,mycall);
  gg=atoi(row[2]);
  sprintf(buf,"select dxcc,cont,cqzone,ituzone from cty");
  mysql_query(con,buf);
  res=mysql_store_result(con);
  for(;;){
    row=mysql_fetch_row(res);
    if(row==NULL)break;
    c=atoi(row[0]);
    strncpy(cont[c],row[1],2);
    cqz[c]=atoi(row[2]);
    ituz[c]=atoi(row[3]);
  }
  mysql_free_result(res);
  sprintf(buf,"select callsign,freqtx,dxcc,contesttx,contestrx,mode from log where contest='%s' and mycall='%s' order by start desc",tok[9],mycall);
  mysql_query(con,buf);
  res=mysql_store_result(con);
  for(;;){
    row=mysql_fetch_row(res);
    if(row==NULL)break;
    c=myband[(int)(atol(row[1])/1000000.0)]/10;
    vv=atoi(row[2]);
    switch(contype){
      case 0: // CQWWSSB
      case 1: // CQWWCW
        sprintf(aux1,"%03d:%s",c,row[0]);
        incdata3(0,0,aux1,1,0);
        if(strncmp(cont[vv],cont[gg],2)!=0)incdata3(0,1,aux1,3,0);
        else if(strncmp(cont[vv],"NA",2)==0 && strncmp(cont[gg],"NA",2)==0 && gg!=vv)incdata3(0,1,aux1,2,0);
        else if(strncmp(cont[vv],cont[gg],2)==0 && gg!=vv)incdata3(0,1,aux1,1,0);
        else incdata3(0,1,aux1,0,0);
        sprintf(aux2,"%03d:%d",c,vv);
        sprintf(aux3,"%03d:Z%d",c,cqz[vv]);
        incdata3(0,2,aux2,1,0); incdata3(0,2,aux3,1,0);
        incdata3(0,3,aux2,1,0); incdata3(0,3,aux3,1,0);
        sprintf(aux4,"%03d",c);
        incdata3(0,4,aux4,1,0);
        break;
      case 2: // CQWPXSSB
      case 3: // CQWPXCW
        sprintf(aux1,"%03d:%s",c,row[0]);
        incdata3(0,0,aux1,1,0);
        if(strncmp(cont[vv],cont[gg],2)!=0){if(c<=20)incdata3(0,1,aux1,3,0); else incdata3(0,1,aux1,6,0);}
        else if(strncmp(cont[vv],"NA",2)==0 && strncmp(cont[gg],"NA",2)==0){if(c<=20)incdata3(0,1,aux1,2,0); else incdata3(0,1,aux1,4,0);}
        else if(gg!=vv){if(c<=20)incdata3(0,1,aux1,1,0); else incdata3(0,1,aux1,2,0);}
        else incdata3(0,1,aux1,1,0);
        sprintf(aux2,"%03d:%s",c,wpx(row[0]));
        sprintf(aux3,"ALL:%s",wpx(row[0]));
        incdata3(0,2,aux2,1,0); incdata3(0,3,aux3,1,0);
        sprintf(aux4,"%03d",c);
        incdata3(0,4,aux4,1,0);
        break;
      case 4: // CQWWDIGI
        sprintf(aux1,"%03d:%s",c,row[0]);
        lat1=((row[3][1]-'A')*10.0+(row[3][3]-'0')+1.0/48.0-90.0);
        lon1=-((row[3][0]-'A')*20.0+(row[3][2]-'0')*2.0+1.0/24.0-180.0);
        lat2=((row[4][1]-'A')*10.0+(row[4][3]-'0')+1.0/48.0-90.0);
        lon2=-((row[4][0]-'A')*20.0+(row[4][2]-'0')*2.0+1.0/24.0-180.0);
        gg=1+distance(lat1,lon1,lat2,lon2)/3000;
        incdata3(0,0,aux1,1,0);
        incdata3(0,1,aux1,gg,0);
        sprintf(aux2,"%03d:%.2s",c,row[4]);
        sprintf(aux3,"ALL:%.2s",row[4]);
        incdata3(0,2,aux2,1,0); incdata3(0,3,aux3,1,0);
        sprintf(aux4,"%03d",c);
        incdata3(0,4,aux4,1,0);
        break;
      case 5: // 4080
        strcpy(aux5,mymode(row[5]));
        sprintf(aux1,"%02d%2s:%s",c,aux5,row[0]);
        incdata3(0,0,aux1,1,0);
        if(strncmp(aux5,"PH",2)==0)incdata3(0,1,aux1,1,0);
        else if(strncmp(aux5,"DG",2)==0)incdata3(0,1,aux1,2,0);
        else if(strncmp(aux5,"CW",2)==0)incdata3(0,1,aux1,3,0);
        sprintf(aux2,"%02d%2s:%.2s",c,aux5,row[4]);
        incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
        sprintf(aux4,"%02d%2s",c,aux5);
        incdata3(0,4,aux4,1,0);
        break;
      case 6: // IARUHF
        sprintf(aux1,"%03d:%s",c,row[0]);
        incdata3(0,0,aux1,1,0);
        if(!isdigit(row[4][0]))incdata3(0,1,aux1,1,0);
        else if(strncmp(cont[vv],cont[gg],2)!=0)incdata3(0,1,aux1,5,0);
        else if(ituz[gg]!=ituz[vv])incdata3(0,1,aux1,3,0);
        else incdata3(0,1,aux1,1,0);
        if(!isdigit(row[4][0]))sprintf(aux2,"%03d:%s",c,row[4]); else sprintf(aux2,"%03d:%d",c,ituz[vv]);
        incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
        sprintf(aux4,"%03d",c);
        incdata3(0,4,aux4,1,0);
        break;
      case 7: // CQ160SSB
      case 8: // CQ160CW
        sprintf(aux1,"%03d:%s",c,row[0]);
        incdata3(0,0,aux1,1,0);
        if(strncmp(cont[vv],cont[gg],2)!=0)incdata3(0,1,aux1,10,0);
        else if(vv!=gg)incdata3(0,1,aux1,5,0);
        else incdata3(0,1,aux1,2,0);
        if(!isdigit(row[4][0]))incdata3(0,1,aux1,1,0);
        else if(strncmp(cont[vv],cont[gg],2)!=0)incdata3(0,1,aux1,5,0);
        else if(ituz[gg]!=ituz[vv])incdata3(0,1,aux1,3,0);
        else incdata3(0,1,aux1,1,0);
        if(!isdigit(row[4][0]))sprintf(aux2,"%03d:%s",c,row[4]); else sprintf(aux2,"%03d:%d",c,vv);
        incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
        sprintf(aux4,"%03d",c);
        incdata3(0,4,aux4,1,0);
        break;
      case 9: // SPDX SP=269
        sprintf(aux1,"%03d:%s:%s",c,row[0],mymode(row[5]));
        incdata3(0,0,aux1,1,0);
        if(gg==269){if(strncmp(cont[vv],"EU",2)!=0)incdata3(0,1,aux1,3,0); else if(vv!=269)incdata3(0,1,aux1,1,0);}
        else if(vv==269)incdata3(0,1,aux1,3,0);
        else incdata3(0,1,aux1,1,0);
        if(gg==269)sprintf(aux2,"%03d:%d",c,vv); else if(vv==269)sprintf(aux2,"%03d:%s",c,row[4]);
        incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
        sprintf(aux4,"%03d",c);
        incdata3(0,4,aux4,1,0);
        break;
      case 10: // LZDX LZ=212
        sprintf(aux1,"%03d:%s:%s",c,row[0],mymode(row[5]));
        incdata3(0,0,aux1,1,0);
        if(vv==212){if(gg==212)incdata3(0,1,aux1,1,0); else incdata3(0,1,aux1,10,0);}
        else {if(strncmp(cont[vv],cont[gg],2)!=0)incdata3(0,1,aux1,3,0); else incdata3(0,1,aux1,1,0);}
        if(gg==212){
          sprintf(aux2,"%03d:%d",c,vv);
          sprintf(aux3,"%03d:Z%d",c,ituz[vv]);
          incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
          incdata3(0,2,aux3,1,0); incdata3(0,3,aux3,1,0);
        }
        else {
          sprintf(aux2,"%03d:Z%d",c,ituz[vv]);
          incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
          if(vv==212){
            sprintf(aux2,"%03d:%s",c,row[4]);
            incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
          }
        }
        sprintf(aux4,"%03d",c);
        incdata3(0,4,aux4,1,0);
        break;
      case 11: // OKOMSSB OK=503 OM=504
      case 12: // OKOMCW OK=503 OM=504
        sprintf(aux1,"%03d:%s",c,row[0]);
        incdata3(0,0,aux1,1,0);
        if(gg==503||gg==504){
          if(gg==vv)incdata3(0,1,aux1,2,0);
          else if(strncmp(cont[vv],cont[gg],2)==0)incdata3(0,1,aux1,3,0);
          else incdata3(0,1,aux1,5,0);
        }
        else {
          if(vv==503||vv==504)incdata3(0,1,aux1,10,0);
          else if(gg==vv)incdata3(0,1,aux1,1,0);
          else if(strncmp(cont[vv],cont[gg],2)==0)incdata3(0,1,aux1,3,0);
          else incdata3(0,1,aux1,5,0);
        }
        if(gg==503||gg==504)sprintf(aux2,"%03d:%s",c,row[4]);
        else sprintf(aux2,"%03d:%d",c,vv);
        incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
        sprintf(aux4,"%03d",c);
        incdata3(0,4,aux4,1,0);
        break;
      case 13: // HADX HA=239
        sprintf(aux1,"%03d:%s:%s",c,row[0],mymode(row[5]));
        incdata3(0,0,aux1,1,0);
        if(vv==239)incdata3(0,1,aux1,10,0);
        else if(strncmp(cont[vv],cont[gg],2)!=0)incdata3(0,1,aux1,5,0);
        else incdata3(0,1,aux1,2,0);
        sprintf(aux2,"%03d:%d",c,vv);
        incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
        if(vv==239){
          sprintf(aux2,"%03d:%s",c,row[4]);
          incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
        }
        sprintf(aux4,"%03d",c);
        incdata3(0,4,aux4,1,0);
        break;
      case 14: // ARIDX IT=248
        sprintf(aux1,"%03d:%s:%s",c,row[0],mymode(row[5]));
        incdata3(0,0,aux1,1,0);
        if(gg==248){
          if(strncmp(cont[vv],cont[gg],2)!=0)incdata3(0,1,aux1,3,0);
          else incdata3(0,1,aux1,1,0);
        }
        else {
          if(gg==vv)incdata3(0,1,aux1,0,0);
          else if(vv==248)incdata3(0,1,aux1,10,0);
          else if(strncmp(cont[vv],cont[gg],2)!=0)incdata3(0,1,aux1,3,0);
          else incdata3(0,1,aux1,1,0);
        }
        sprintf(aux2,"%03d:%d",c,vv);
        incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
        if(vv==248){
          sprintf(aux2,"%03d:%s",c,row[4]);
          incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
        }
        sprintf(aux4,"%03d",c);
        incdata3(0,4,aux4,1,0);
        break;
      case 15: // KOSSSB EA=281 EA6=21 EA9=32 EA8=29
      case 16: // KOSCW EA=281 EA6=21 EA9=32 EA8=29
        sprintf(aux1,"%03d:%s",c,row[0]);
        incdata3(0,0,aux1,1,0);
        if(gg==281 || gg==21 || gg==32 || gg==29){
          if(vv==281 || vv==21 || vv==32 || vv==29)incdata3(0,1,aux1,2,0);
          else incdata3(0,1,aux1,1,0);
        }
        else {
          if(vv==281 || vv==21 || vv==32 || vv==29)incdata3(0,1,aux1,3,0);
          else incdata3(0,1,aux1,1,0);
        }
        sprintf(aux2,"%03d:%d",c,vv);
        incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
        if(vv==281 || vv==21 || vv==32 || vv==29){
          sprintf(aux2,"%03d:%s",c,row[4]);
          incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
        }
        sprintf(aux4,"%03d",c);
        incdata3(0,4,aux4,1,0);
        break;
      case 17: // RDAC UAinEU=54 UAinAS=15
        sprintf(aux1,"%03d:%s:%s",c,row[0],mymode(row[5]));
        incdata3(0,0,aux1,1,0);
        if(gg==54 || gg==15){
          if(vv==gg)incdata3(0,1,aux1,1,0);
          else if((vv==54 || vv==15) && vv!=gg)incdata3(0,1,aux1,2,0);
          else if(strncmp(cont[vv],cont[gg],2)==0)incdata3(0,1,aux1,3,0);
          else incdata3(0,1,aux1,5,0);
        }
        else {
          if(vv==54 || vv==15)incdata3(0,1,aux1,10,0);
        }
        if(gg==54 || gg==15){
          sprintf(aux2,"%03d:%d",c,vv);
          incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
        }
        if(vv==54 || vv==15){
          sprintf(aux2,"%03d:%s",c,row[4]);
          incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
        }
        sprintf(aux4,"%03d",c);
        incdata3(0,4,aux4,1,0);
        break;
      case 18: // ARRLSSB W=291 VE=1
      case 19: // ARRLCW W=291 VE=1
        sprintf(aux1,"%03d:%s",c,row[0]);
        incdata3(0,0,aux1,1,0);
        incdata3(0,1,aux1,3,0);
        if(gg==291 || gg==1){
          sprintf(aux2,"%03d:%d",c,vv);
          incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
        }
        else {
          sprintf(aux2,"%03d:%s",c,row[4]);
          incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
        }
        sprintf(aux4,"%03d",c);
        incdata3(0,4,aux4,1,0);
        break;
      case 20: // RDXC UAinEU=54 UAinAS=15
        sprintf(aux1,"%03d:%s:%s",c,row[0],mymode(row[5]));
        incdata3(0,0,aux1,1,0);
        if(gg==54 || gg==15){
          if(vv==gg)incdata3(0,1,aux1,2,0);
          else if((vv==54 || vv==15) && strncmp(cont[vv],cont[gg],2)!=0)incdata3(0,1,aux1,5,0);
          else if(strncmp(cont[vv],cont[gg],2)==0)incdata3(0,1,aux1,3,0);
          else incdata3(0,1,aux1,5,0);
        }
        else {
          if(vv==54 || vv==15)incdata3(0,1,aux1,10,0);
          else if(gg==vv)incdata3(0,1,aux1,2,0);
          else if(strncmp(cont[vv],cont[gg],2)==0)incdata3(0,1,aux1,3,0);
          else incdata3(0,1,aux1,5,0);
        }
        sprintf(aux2,"%03d:%d",c,vv);
        incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
        if(vv==54 || vv==15){
          sprintf(aux2,"%03d:%s",c,row[4]);
          incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
        }
        sprintf(aux4,"%03d",c);
        incdata3(0,4,aux4,1,0);
        break;
      case 21: // JIDXSSB JA=339
      case 22: // JIDXCW JA=339  
        sprintf(aux1,"%03d:%s:%s",c,row[0],mymode(row[5]));
        incdata3(0,0,aux1,1,0);
        if((gg==339 && vv==339) || (gg!=339 && vv!=339))incdata3(0,1,aux1,0,0);
        else if(c==160)incdata3(0,1,aux1,4,0);
        else if(c==80 || c==10)if(c==160)incdata3(0,1,aux1,2,0);
        else incdata3(0,1,aux1,1,0);
        if(gg==339){
          sprintf(aux2,"%03d:%d",c,vv);
          incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
          sprintf(aux2,"%03d:%d",c,cqz[vv]);
          incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
        }
        else {
          sprintf(aux2,"%03d:%s",c,row[4]);
          incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
        }
        sprintf(aux4,"%03d",c);
        incdata3(0,4,aux4,1,0);
        break;
      case 23: // YODX YO=275 
        sprintf(aux1,"%03d:%s:%s",c,row[0],mymode(row[5]));
        incdata3(0,0,aux1,1,0);
        if(gg==275){
          if(vv==275)incdata3(0,1,aux1,0,0);
          else if(strncmp(cont[vv],"EU",2)==0)incdata3(0,1,aux1,4,0);
          else incdata3(0,1,aux1,8,0);
        }
        else {
          if(vv==275)incdata3(0,1,aux1,8,0);
          else if(strncmp(cont[vv],cont[gg],2)!=0)incdata3(0,1,aux1,4,0);
          else if(vv==gg)incdata3(0,1,aux1,1,0);
          else incdata3(0,1,aux1,2,0);
        }
        sprintf(aux2,"%03d:%d",c,vv);
        incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
        if(gg!=275){
          sprintf(aux2,"%03d:%s",c,row[4]);
          incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
        }
        sprintf(aux4,"%03d",c);
        incdata3(0,4,aux4,1,0);
        break;
      case 24: // CQM
        sprintf(aux1,"%03d:%s:%s",c,row[0],mymode(row[5]));
        incdata3(0,0,aux1,1,0);
        if(strncmp(cont[vv],cont[gg],2)==0)incdata3(0,1,aux1,2,0);
        else if(strncmp(cont[gg],"EU",2)==0 && strncmp(cont[vv],"AS",2)==0)incdata3(0,1,aux1,2,0);
        else if(strncmp(cont[vv],"EU",2)==0 && strncmp(cont[gg],"AS",2)==0)incdata3(0,1,aux1,2,0);
        else incdata3(0,1,aux1,3,0);
        sprintf(aux2,"%03d:%d",c,vv);
        incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
        sprintf(aux4,"%03d",c);
        incdata3(0,4,aux4,1,0);
        break;
      case 25: // WAESSB (no QTC)
      case 26: // WAECW (no QTC)
      case 27: // WAERTTY (no QTC)
        sprintf(aux1,"%03d:%s",c,row[0]);
        incdata3(0,0,aux1,1,0);
        incdata3(0,1,aux1,1,0);
        d=0; if(c==80)d=4; else if(c==40)d=3; else if(c==20||c==15||c==10)d=2;
        if(strncmp(cont[gg],"EU",2)!=0){
          if(strncmp(cont[vv],"EU",2)==0){
            sprintf(aux2,"%03d:%d",c,vv);
            incdata3(0,2,aux2,d,0); incdata3(0,3,aux2,d,0);
          }
        }
        else {
          if(strncmp(cont[vv],"EU",2)!=0){
            if(vv==291||vv==1||vv==150||vv==170||vv==462||vv==339||vv==318||vv==108){
              for(p=row[0];*p!='\0';p++)if(isdigit(*p))break;
              sprintf(aux2,"%03d:%d:%c",c,vv,*p);
            }
            else sprintf(aux2,"%03d:%d",c,vv);
            incdata3(0,2,aux2,d,0); incdata3(0,3,aux2,d,0);
          }
        }                                
        sprintf(aux4,"%03d",c);
        incdata3(0,4,aux4,1,0);
        break;
      case 28: // CQ28
        if(gg!=248||vv!=248)break;
        sprintf(aux1,"%03d:%s:%s",c,row[0],mymode(row[5]));
        incdata3(0,0,aux1,1,0);
        if(strlen(row[4])==2){
          sprintf(aux2,"%03d:%s",c,row[4]);
          if(numdata3(0,2,aux2)==0)incdata3(0,1,aux1,5,0);
          else incdata3(0,1,aux1,1,0);
          incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
        }
        else {
          incdata3(0,1,aux1,10,0);
          sprintf(aux2,"%03d:%.2s",c,row[4]);
          incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
          sprintf(aux2,"%03d:%d",c,atoi(row[4]+2));
          incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
        }
        sprintf(aux4,"%03d",c);
        incdata3(0,4,aux4,1,0);
        break;
      case 29: // UBASSB ON=209
      case 30: // UBACW ON=209
        sprintf(aux1,"%03d:%s",c,row[0]);
        incdata3(0,0,aux1,1,0);
        if(gg==209){
          if(vv==209)incdata3(0,1,aux1,1,0);
          else if(strncmp(cont[vv],"EU",2)==0)incdata3(0,1,aux1,2,0);
          else incdata3(0,1,aux1,3,0);
        }
        else {
          if(vv==209)incdata3(0,1,aux1,10,0);
          else if(strncmp(cont[vv],"EU",2)==0)incdata3(0,1,aux1,3,0);
          else incdata3(0,1,aux1,1,0);
        }
        if(gg==209){
          sprintf(aux2,"%03d:%d",c,vv);
          incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
        }
        else {
          sprintf(aux2,"%03d:%s",c,row[4]);
          incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
          if(vv==209){
            for(p=row[0];*p!='\0';p++)if(isdigit(*p))break;
            sprintf(aux2,"%03d:%d:%c",c,vv,*p);
            incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
          }
          int lll[] = {215, 497, 257, 272, 256, 149, 230, 281, 21, 29, 245, 52, 227, 79, 95, 169, 63, 239, 248, 225, 254, 146, 212, 206, 224, 211, 183, 503, 504, 221, 263, 499, 284, 269, 236, 45, 40, 180, 214, 145, 275};
          n=sizeof(lll)/sizeof(lll[0]);
          for(d=0;d<n;d++)if(vv==lll[d])break;
          if(d<n){
            sprintf(aux2,"%03d:%d",c,vv);
            incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
          }
        }
        sprintf(aux4,"%03d",c);
        incdata3(0,4,aux4,1,0);
        break;
      case 31: // IOTA (no iscland)
        sprintf(aux1,"%03d:%s:%s",c,row[0],mymode(row[5]));
        incdata3(0,0,aux1,1,0);
        for(p=row[4];*p!='\0';p++)if(!isdigit(*p))break;
        if(*p!='\0')incdata3(0,1,aux1,15,0);
        else incdata3(0,1,aux1,2,0);
        if(*p!='\0'){
          sprintf(aux2,"%03d:%s:%s",c,p,mymode(row[5]));
          incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
        }
        sprintf(aux4,"%03d",c);
        incdata3(0,4,aux4,1,0);
        break;
      case 32: // EUHF
        sprintf(aux1,"%03d:%s:%s",c,row[0],mymode(row[5]));
        incdata3(0,0,aux1,1,0);
        incdata3(0,1,aux1,1,0);
        sprintf(aux2,"%03d:%s",c,row[4]);
        incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
        sprintf(aux4,"%03d",c);
        incdata3(0,4,aux4,1,0);
        break;
      case 33: // ARISEZ
        if(gg!=248||vv!=248)break;
        sprintf(aux1,"%03d:%s:%s",c,row[0],mymode(row[5]));
        incdata3(0,0,aux1,1,0);
        if(c==40)incdata3(0,1,aux1,1,0);
        else if(c==80||c==10)incdata3(0,1,aux1,2,0);
        else if(c==160||c==15)incdata3(0,1,aux1,3,0);
        else if(c==10)incdata3(0,1,aux1,4,0);
        sprintf(aux2,"%03d:%s:%s",c,row[4],mymode(row[5]));
        incdata3(0,2,aux2,1,0); incdata3(0,3,aux2,1,0);
        sprintf(aux4,"%03d",c);
        incdata3(0,4,aux4,1,0);
        break;
    }
  }
  mysql_free_result(res);
}
