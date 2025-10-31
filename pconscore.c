// pconscore.c contest score function by GM @2025 V 2.0

void conscore(void){
  const char *conid[]={"CQWWSSB","CQWWCW","CQWPXSSB","CQWPXCW","CQWWDIGI","4080"};
  
    for(contype=0;contype<6;contype++)if(strncmp(tok[9],conid[contype],strlen(conid[contype]))==0)break;
    if(contype==6)goto end;
    for(l1=0;l1<TOT3;l1++)for(l2=0;l2<TOTL2;l2++)ndata3[l1][l2]=0;
    printf("<pre>");
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
    gg=248;
    for(;;){
      row=mysql_fetch_row(res);
      if(row==NULL)break;
      c=myband[(int)(atol(row[1])/1000000.0)]/10;
      vv=atoi(row[2]);
      if(contype==0||contype==1){ // CQWWSSB e CQWWCW
        sprintf(aux1,"%03d:%s",c,row[0]);
        sprintf(aux2,"%03d:%d",c,vv);
        sprintf(aux3,"%03d:Z%d",c,cqz[vv]);
        sprintf(aux4,"%03d",c);
        incdata3(0,0,aux1,1,0);
        if(strncmp(cont[vv],cont[gg],2)!=0)incdata3(0,1,aux1,3,0);
        else if(strncmp(cont[vv],"NA",2)==0 && strncmp(cont[gg],"NA",2)==0 && gg!=vv)incdata3(0,1,aux1,2,0);
        else if(strncmp(cont[vv],cont[gg],2)==0 && gg!=vv)incdata3(0,1,aux1,1,0);
        else incdata3(0,1,aux1,0,0);
        incdata3(0,2,aux2,1,0); incdata3(0,2,aux3,1,0);
        incdata3(0,3,aux2,1,0); incdata3(0,3,aux3,1,0);
        incdata3(0,4,aux4,1,0);
      }
      else if(contype==2||contype==3){ // CQWPXSSB e CQWPXCW
        sprintf(aux1,"%03d:%s",c,row[0]);
        sprintf(aux2,"%03d:%s",c,wpx(row[0]));
        sprintf(aux3,"ALL:%s",wpx(row[0]));
        sprintf(aux4,"%03d",c);
        incdata3(0,0,aux1,1,0);
        if(strncmp(cont[vv],cont[gg],2)!=0){if(c<=20)incdata3(0,1,aux1,3,0); else incdata3(0,1,aux1,6,0);}
        else if(strncmp(cont[vv],"NA",2)==0 && strncmp(cont[gg],"NA",2)==0){if(c<=20)incdata3(0,1,aux1,2,0); else incdata3(0,1,aux1,4,0);}
        else if(gg!=vv){if(c<=20)incdata3(0,1,aux1,1,0); else incdata3(0,1,aux1,2,0);}
        else incdata3(0,1,aux1,1,0);
        incdata3(0,2,aux2,1,0);
        incdata3(0,3,aux3,1,0);
        incdata3(0,4,aux4,1,0);
      }
      else if(contype==4){ // CQWWDIGI
        sprintf(aux1,"%03d:%s",c,row[0]);
        lat1=((row[3][1]-'A')*10.0+(row[3][3]-'0')+1.0/48.0-90.0);
        lon1=-((row[3][0]-'A')*20.0+(row[3][2]-'0')*2.0+1.0/24.0-180.0);
        lat2=((row[4][1]-'A')*10.0+(row[4][3]-'0')+1.0/48.0-90.0);
        lon2=-((row[4][0]-'A')*20.0+(row[4][2]-'0')*2.0+1.0/24.0-180.0);
        gg=1+distance(lat1,lon1,lat2,lon2)/3000;
        sprintf(aux2,"%03d:%.2s",c,row[4]);
        sprintf(aux3,"ALL:%.2s",row[4]);
        sprintf(aux4,"%03d",c);
        incdata3(0,0,aux1,1,0);
        incdata3(0,1,aux1,gg,0);
        incdata3(0,2,aux2,1,0);
        incdata3(0,3,aux3,1,0);
        incdata3(0,4,aux4,1,0);
      }
      else if(contype==5){ // 4080
        strcpy(aux5,mymode(row[5]));
        sprintf(aux1,"%02d%2s:%s",c,aux5,row[0]);
        sprintf(aux2,"%02d%2s:%.2s",c,aux5,row[4]);
        sprintf(aux3,"%02d%2s:%.2s",c,aux5,row[4]);
        sprintf(aux4,"%02d%2s",c,aux5);
        incdata3(0,0,aux1,1,0);
        if(strncmp(aux5,"PH",2)==0)incdata3(0,1,aux1,1,0);
        else if(strncmp(aux5,"DG",2)==0)incdata3(0,1,aux1,2,0);
        else if(strncmp(aux5,"CW",2)==0)incdata3(0,1,aux1,3,0);
        incdata3(0,2,aux2,1,0);
        incdata3(0,3,aux3,1,0);
        incdata3(0,4,aux4,1,0);
      }
    }
