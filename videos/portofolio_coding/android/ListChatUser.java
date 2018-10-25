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
import android.view.View;
import android.widget.AdapterView;
import android.widget.EditText;
import android.widget.ListAdapter;
import android.widget.ListView;
import android.widget.SimpleAdapter;
import android.widget.TextView;
import android.widget.AdapterView.OnItemClickListener;

public class ListChatUser extends ListActivity{
	
	private EditText search_on;
	private ListAdapter adapter;
	private static final String IDUSER = "id";
	private String ID_USER;
	
	public void onCreate(Bundle reqlist){
		super.onCreate(reqlist);
		setContentView(R.layout.form_online_list);
		Intent in = getIntent();
        ID_USER = in.getStringExtra(IDUSER);
        
		showListChat lc = new showListChat();
		lc.execute("http://192.168.1.73:8080/chat/list_chat.php?id="+ID_USER);
		
		search_on = (EditText) findViewById(R.id.txt_search_on);
		
		search_on.addTextChangedListener(new TextWatcher() {

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
				((SimpleAdapter) ListChatUser.this.adapter).getFilter().filter(s);
				
			}
           
        });
    }
	
	class showListChat extends AsyncTask<String, Integer, ArrayList<HashMap<String, String>> >{
		
		ArrayList<HashMap<String, String>> mylist = new ArrayList<HashMap<String, String>>();

		@Override
		protected ArrayList<HashMap<String, String>> doInBackground(String... params) {
			// TODO Auto-generated method stub
			try {
				JSONObject json = JSONParser.getJSONfromURL(params[0]); 
				JSONArray list_chat = json.getJSONArray("list_chat");	

				for (int i = 0; i < list_chat.length(); i++){
					JSONObject jsonobj = list_chat.getJSONObject(i);
					
					HashMap<String, String> map = new HashMap<String, String>();
					
					map.put("id",  jsonobj.getString("dari"));
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
			
			adapter = new SimpleAdapter(ListChatUser.this, mylist , 
					R.layout.data_online_list,
					new String[] { "id", "nama", "angkatan", "status_profile"},
					new int[] { R.id.txt_id_user, R.id.txt_nama, R.id.txt_angkatan, R.id.txt_status_user_on});
			setListAdapter(adapter);
	        
	        final ListView lv = getListView();
	        lv.setOnItemClickListener(new OnItemClickListener() {
	        	public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
	        		
	        		String id_user = ((TextView) view.findViewById(R.id.txt_id_user)).getText().toString();
	        		String to_nama = ((TextView) view.findViewById(R.id.txt_nama)).getText().toString();
					Intent in = new Intent(ListChatUser.this, ChatPrivate.class);
					in.putExtra("id_user", id_user);
					in.putExtra("id", ID_USER);
					in.putExtra("to_nama", to_nama);
					startActivity(in);
				}
			});
		
		}

	}

}
