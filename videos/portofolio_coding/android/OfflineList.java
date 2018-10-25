package harus.bisa;

import java.util.ArrayList;
import java.util.HashMap;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;
import android.app.ListActivity;
import android.content.Intent;
import android.os.AsyncTask;
import android.os.Bundle;
import android.text.Editable;
import android.text.TextWatcher;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.AdapterView;
import android.widget.AdapterView.OnItemClickListener;
import android.widget.EditText;
import android.widget.ListAdapter;
import android.widget.ListView;
import android.widget.SimpleAdapter;
import android.widget.TextView;

public class OfflineList extends ListActivity{
	
	private static final String IDUSER = "id";
	private String ID_USER;
	private ListAdapter adapter;
	private EditText search_off;
	Menu menu;
	
	public void onCreate(Bundle onlist){
		super.onCreate(onlist);
		setContentView(R.layout.form_offline_list);
		showOfflineList off = new showOfflineList();
		Intent in = getIntent();
        ID_USER = in.getStringExtra(IDUSER);
		off.execute("http://192.168.1.73:8080/chat/data_offline_list_alphabet.php?id_user="+ID_USER);
		
		search_off = (EditText) findViewById(R.id.txt_search_off);
		search_off.addTextChangedListener(new TextWatcher() {

			public void afterTextChanged(Editable s) {
				// TODO Auto-generated method stub
				
			}

			public void beforeTextChanged(CharSequence s, int start, int count,
					int after) {
				// TODO Auto-generated method stub
				
			}

			public void onTextChanged(CharSequence s, int start, int before,
					int count) {
				// TODO Auto-generated method stub
				((SimpleAdapter) OfflineList.this.adapter).getFilter().filter(s);
				
			}
			 
           
        });
	}
	
	class showOfflineList extends AsyncTask<String, Integer, ArrayList<HashMap<String, String>> >{
	
		ArrayList<HashMap<String, String>> mylist = new ArrayList<HashMap<String, String>>();

		@Override
		protected ArrayList<HashMap<String, String>> doInBackground(String... params) {
			// TODO Auto-generated method stub
			try {
				JSONObject json = JSONParser.getJSONfromURL(params[0]); 
				JSONArray data_ol = json.getJSONArray("data_offline_list");	

				for (int i = 0; i < data_ol.length(); i++){
					JSONObject jsonobj = data_ol.getJSONObject(i);
					
					HashMap<String, String> map = new HashMap<String, String>();

					map.put("id",  jsonobj.getString("id"));
		        	map.put("nama", jsonobj.getString("nama"));
		        	map.put("angkatan", jsonobj.getString("angkatan"));
		        	map.put("status_profile", jsonobj.getString("status_profile"));
		        	mylist.add(map);
				}
			} catch (JSONException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}
			return null;
		}
		
		protected void onPostExecute(ArrayList<HashMap<String, String>> result) {
			
			adapter = new SimpleAdapter(OfflineList.this, mylist , 
					R.layout.data_offline_list,
					new String[] { "id", "nama", "angkatan", "status_profile"},
					new int[] { R.id.txt_id_user_off, R.id.txt_nama_off, R.id.txt_angkatan_off, R.id.txt_status_user_off});
	        setListAdapter(adapter);
	       
	        
	        final ListView lv = getListView();
	        lv.setOnItemClickListener(new OnItemClickListener() {
	        	public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
	        		
	        		String id_ke = ((TextView) view.findViewById(R.id.txt_id_user_off)).getText().toString();
	        		String to_nama = ((TextView) view.findViewById(R.id.txt_nama_off)).getText().toString();
					Intent in = new Intent(OfflineList.this, ChatPrivate.class);
					in.putExtra("id_user", id_ke);
					in.putExtra("id", ID_USER);
					in.putExtra("to_nama", to_nama);
					startActivity(in);
				}
			});
		
		}
	}
	
	@Override
	public boolean onCreateOptionsMenu(Menu menu) {
		this.menu = menu;
		
		menu.add(0, 1, 0, "Refresh").setIcon(android.R.drawable.ic_popup_sync);
	    menu.add(0, 2, 0, "Sort alphabetically").setIcon(android.R.drawable.ic_menu_sort_alphabetically);
	    menu.add(0, 3, 0, "Sort by year").setIcon(android.R.drawable.ic_menu_today);
		return true;
	}
	
	@Override
	public boolean onOptionsItemSelected(MenuItem item) {
	    switch (item.getItemId()) {
	    case 1:
	    	showOfflineList off = new showOfflineList();
			Intent in = getIntent();
	        String ID_USER_2 = in.getStringExtra(IDUSER);
			off.execute("http://192.168.1.73:8080/chat/data_offline_list_alphabet.php?id_user="+ID_USER_2);
	        return true;
	    case 2:
	    	showOfflineList off_a = new showOfflineList();
			Intent i = getIntent();
	        String ID_USER_3 = i.getStringExtra(IDUSER);
			off_a.execute("http://192.168.1.73:8080/chat/data_offline_list_alphabet.php?id_user="+ID_USER_3);
	        return true;
	    case 3:
	    	showOfflineList off_y = new showOfflineList();
			Intent intent = getIntent();
	        String ID_USER_4 = intent.getStringExtra(IDUSER);
			off_y.execute("http://192.168.1.73:8080/chat/data_offline_list_year.php?id_user="+ID_USER_4);
	        return true;
	    }
		return false;
	}
}
