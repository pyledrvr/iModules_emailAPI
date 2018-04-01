CREATE OR REPLACE PACKAGE iModules_emailAPI IS

  PROCEDURE im_emails_all_headers_setup;
  procedure imodules_all_email_recipients;
  procedure imodules_all_email_opens;
  PROCEDURE imodules_all_email_clicks;
  procedure imodules_email_update_xcomment;

END iModules_emailAPI;
/
CREATE OR REPLACE PACKAGE BODY iModules_emailAPI IS
  PROCEDURE imodules_all_email_recipients IS
    cursor c1 is
      SELECT ID, MEMBERID, CONSTITUENTID, EMAILADDRESS, EMAIL_HEADER_ID
        FROM IM_EMAIL_RECIPIENTS_TMP;
  
    v_ID              IM_EMAIL_RECIPIENTS_TMP.ID%type;
    v_MEMBERID        IM_EMAIL_RECIPIENTS_TMP.MEMBERID%type;
    v_CONSTITUENTID   IM_EMAIL_RECIPIENTS_TMP.CONSTITUENTID%type;
    v_EMAILADDRESS    IM_EMAIL_RECIPIENTS_TMP.EMAILADDRESS%type;
    v_EMAIL_HEADER_ID IM_EMAIL_RECIPIENTS_TMP.EMAIL_HEADER_ID%type;
  
    v_count           integer := 0;
    v_count_read      integer := 0;
    v_count_not_found integer := 0;
    v_commit_counter  integer := 0;
  
  BEGIN
    --insert new records into IM_EMAIL_RECIPIENTS
    begin
      open c1;
      loop
        fetch c1
          into v_ID,
               v_MEMBERID,
               v_CONSTITUENTID,
               v_EMAILADDRESS,
               v_EMAIL_HEADER_ID;
        exit when c1%notfound;
        v_count_read := v_count_read + 1;
        select count(*)
          into v_count
          from IM_EMAIL_RECIPIENTS a
         where EMAIL_HEADER_ID = v_EMAIL_HEADER_ID
           and ID = v_ID;
        if v_count = 0 then
          v_count_not_found := v_count_not_found + 1;
          INSERT INTO IM_EMAIL_RECIPIENTS
            (ID, MEMBERID, CONSTITUENTID, EMAILADDRESS, EMAIL_HEADER_ID)
          values
            (v_ID,
             v_MEMBERID,
             v_CONSTITUENTID,
             v_EMAILADDRESS,
             v_EMAIL_HEADER_ID);
          v_commit_counter := v_commit_counter + 1;
        end if;
        if v_commit_counter > 250 then
          commit;
          v_commit_counter := 0;
        end if;
      end loop;
      close c1;
      commit;
    end;
    COMMIT;
  
  END imodules_all_email_recipients;

  PROCEDURE imodules_all_email_opens IS
  
    v_ID    IM_EMAIL_RECIPIENTS_TMP.ID%type;
    v_count integer := 0;
  BEGIN
    --update open_date in IM_EMAIL_RECIPIENTS
    select count(recipientid), max(EMAIL_HEADER_ID)
      into v_count, v_id
      from IM_EMAIL_OPENS_TMP;
    if v_count > 0 then
      Quality_Assurance.ex_location := 'imodules_proc.imodules_all_email_opens';
      Quality_Assurance.ex_msg      := 'update open_date in IM_EMAIL_RECIPIENTS';
      update IM_EMAIL_RECIPIENTS ier2
         set ier2.open_date =
             (select max(TO_DATE('1970-01-01', 'YYYY-MM-DD') +
                         timestamp / 86400000)
                from IM_EMAIL_OPENS_TMP
               where IM_EMAIL_OPENS_TMP.EMAIL_HEADER_ID =
                     ier2.email_header_id
                 and IM_EMAIL_OPENS_TMP.RECIPIENTID = ier2.id)
       where ier2.rowid in
             (select ier.rowid
                from IM_EMAIL_OPENS_TMP t, IM_EMAIL_RECIPIENTS ier
               where t.email_header_id = ier.email_header_id
                 and t.recipientid = ier.id)
         and ier2.open_date is null;
      Quality_Assurance.ex_location := 'imodules_proc.imodules_all_email_opens';
      Quality_Assurance.ex_msg      := 'Updated ' || v_count ||
                                       ' records for email id: ' || v_ID;
      log_proc(Quality_Assurance.ex_location, Quality_Assurance.ex_msg);
      imodules_proc.imodules_email_update_xcomment;
    end if;
    COMMIT;
  
  END imodules_all_email_opens;

  PROCEDURE imodules_all_email_clicks IS
    v_ID    IM_EMAIL_RECIPIENTS_TMP.ID%type;
    v_count integer := 0;
  BEGIN
    select count(recipientid), max(EMAIL_HEADER_ID)
      into v_count, v_id
      from IM_EMAIL_clickS_TMP;
    if v_count > 0 then
      --update click_date in IM_EMAIL_RECIPIENTS
      update IM_EMAIL_RECIPIENTS ier2
         set ier2.click_date =
             (select max(TO_DATE('1970-01-01', 'YYYY-MM-DD') +
                         timestamp / 86400000)
                from IM_EMAIL_clicks_TMP
               where IM_EMAIL_clicks_TMP.EMAIL_HEADER_ID =
                     ier2.email_header_id
                 and IM_EMAIL_clicks_TMP.RECIPIENTID = ier2.id)
       where ier2.rowid in
             (select ier.rowid
                from IM_EMAIL_clicks_TMP t, IM_EMAIL_RECIPIENTS ier
               where t.email_header_id = ier.email_header_id
                 and t.recipientid = ier.id)
         and ier2.click_date is null;
      /* Quality_Assurance.ex_location := 'imodules_proc.imodules_all_email_clicks';
      Quality_Assurance.ex_msg      := 'Updated ' || v_count ||
                                       ' records for email id: ' || v_ID;
      log_proc(Quality_Assurance.ex_location, Quality_Assurance.ex_msg);*/
    end if;
    COMMIT;
  
  END imodules_all_email_clicks;

  procedure imodules_email_update_xcomment is
    cursor c1 is
      select t.constituentid,
             upper(t.emailaddress) as emailaddress,
             max(t.open_date) as open_date
        from IM_EMAIL_RECIPIENTS t
       where t.open_date > sysdate - 10
         and t.constituentid is not null
       group by t.constituentid, upper(t.emailaddress);
  
    v_CONSTITUENTID IM_EMAIL_RECIPIENTS.CONSTITUENTID%type;
    v_emailaddress  IM_EMAIL_RECIPIENTS.Emailaddress%type;
    v_open_date     IM_EMAIL_RECIPIENTS.open_date%type;
    v_rowid         rowid;
  
    v_count          integer := 0;
    v_count_read     integer := 0;
    v_count_found    integer := 0;
    v_commit_counter integer := 0;
  
  BEGIN
    --Updating xcomment in email with latest read date.
    begin
      open c1;
      loop
        fetch c1
          into v_CONSTITUENTID, v_EMAILADDRESS, v_open_date;
        exit when c1%notfound;
        v_count_read := v_count_read + 1;
        select count(*), max(rowid)
          into v_count, v_rowid
          from advance.email
         where advance.email.id_number = v_CONSTITUENTID
           and upper(advance.email.email_address) = v_EMAILADDRESS
           and email_status_code = 'A'
           and email_type_code <> 'Z'
           and xcomment not like 'email opened:' || v_open_date || '%';
        if v_count > 0 then
          v_count_found := v_count_found + 1;
          update advance.email
             set advance.email.nbr_bouncebacks = 0,
                 advance.email.xcomment        = 'email opened:' ||
                                                 v_open_date
           where advance.email.rowid = v_rowid;
          v_commit_counter := v_commit_counter + 1;
        end if;
        if v_commit_counter > 250 then
          commit;
          v_commit_counter := 0;
        end if;
      end loop;
      close c1;
      commit;
    end;
    commit;
  end imodules_email_update_xcomment;

  PROCEDURE im_emails_all_headers_setup IS
  BEGIN
    --insert new records into IM_EMAIL_HEADERS
    insert into IM_EMAIL_HEADERS
      (id,
       subcommunityid,
       emailname,
       fromname,
       fromaddress,
       subjectline,
       preheader,
       sentcount,
       categoryname,
       send_date)
      select id,
             subcommunityid,
             emailname,
             fromname,
             fromaddress,
             subjectline,
             preheader,
             sentcount,
             categoryname,
             TO_DATE('1970-01-01', 'YYYY-MM-DD') +
             t.actualsendtimestamp / 86400000 as send_date
        from IM_EMAIL_HEADERS_tmp t
       where t.id not in (select id from IM_EMAIL_HEADERS);
    COMMIT;
  
  END im_emails_all_headers_setup;

END iModules_emailAPI;
/
