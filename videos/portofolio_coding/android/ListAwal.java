package harus.bisa;

import java.util.ArrayList;

import org.apache.http.NameValuePair;
import org.apache.http.message.BasicNameValuePair;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.DialogInterface;
import android.content.Intent;
import android.os.Bundle;
import android.view.KeyEvent;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ListView;
import android.widget.Toast;

public class ListAwal extends Activity {
	Menu menu;
	String id;
	
	public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.list_awal);
        
        Intent i = getIntent();
        id = i.getStringExtra("id");
        
        Choice data[] = new Choice[]{
        		new Choice(R.drawable.icononline, "Online list"),
        		new Choice(R.drawable.iconoffline, "Offline List"),
        		new Choice(android.R.drawable.btn_star_big_on, "Room"),
        		new Choice(R.drawable.chat, "Chat"),
        		new Choice(R.drawable.logout, "Log out")
        };
        
        CategoryAdapter adapter = new CategoryAdapter(this, R.layout.isi_list_awal, data);
        final ListView lv = (ListView) findViewById(R.id.list_awal);
        lv.setAdapter(adapter);
        lv.setClickable(true);
        
        lv.setOnItemClickListener(new AdapterView.OnItemClickListener() {
        	
			public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
				// TODO Auto-generated method stub
				
				Object obj = lv.getItemAtPosition(position);
	        	String option = obj.toString();
	        	option = "" + (id+1);
	        	System.out.println("Opt "+option);
	        	showList(option);
			}
        	
		});
    }
	
	private void showList(String option){
		try{
			Intent i = null;
			if(option.equals("1")){
				i = new Intent(this, OnlineList.class);
				i.putExtra("id", id);
				startActivity(i);
			}else if(option.equals("2")){
				i = new Intent(this, OfflineList.class);
				i.putExtra("id", id);
				startActivity(i);
			}else if(option.equals("3")){
				
			}else if(option.equals("4")){
				i = new Intent(this, ListChatUser.class);
				i.putExtra("id", id);
				startActivity(i);
			}else if(option.equals("5")){
				signOut();
	    		Toast.makeText(getApplicationContext(), "Good bye", Toast.LENGTH_LONG).show();
	    		finish();
			}
		}catch(Exception ex){
			System.out.println("Eaa salah");
		}
	}
	
	public String signOut(){
		ArrayList<NameValuePair> info_input = new ArrayList<NameValuePair>();
		info_input.add(new BasicNameValuePair("id", getIntent().getStringExtra("id")));
        
		String response = null;
		try {
			response = Connect.executeHttpPost("http://192.168.1.73:8080/chat/signout.php", info_input);
			} catch (Exception e) {
				e.printStackTrace();
				}
		return response;
	}
	
	@Override
	public boolean onCreateOptionsMenu(Menu menu) {
		this.menu = menu;
		
		menu.add(0, 1, 0, "Profile").setIcon(R.drawable.iconprofile);
		menu.add(0, 2, 0, "About").setIcon(R.drawable.iconinfo);
		menu.add(0, 3, 0, "Send Request").setIcon(R.drawable.iconreq);
		menu.add(0, 4, 0, "Status").setIcon(R.drawable.status);
	  
		return true;
	}

	@Override
	public boolean onOptionsItemSelected(MenuItem item) {
	    switch (item.getItemId()) {
	    case 1:
	    	
	        return true;
	    case 2:            
	    	
	    case 3:
	    	
	    	return true;
	    case 4:
	    	
	        return true;
	    }
		return false;
	}
	
	@Override
    public boolean onKeyUp(int keyCode, KeyEvent event) {
        if (keyCode == KeyEvent.KEYCODE_BACK) {
        	AlertDialog.Builder adb = new AlertDialog.Builder(ListAwal.this);
        	adb.setMessage("Are you sure to log out?");
        	adb.setCancelable(false);
        	
        	adb.setPositiveButton("Yes", new DialogInterface.OnClickListener() {
    			public void onClick(DialogInterface dialog, int which) {
    				signOut();
    				Toast.makeText(getApplicationContext(), "Good bye", Toast.LENGTH_LONG).show();
    				finish();
    			}
    		});
        	adb.setNegativeButton("No", null);
    		adb.show();			
        }
        return false;
    }
	
	
}
