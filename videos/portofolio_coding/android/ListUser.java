package harus.bisa;

import java.util.ArrayList;
import java.util.HashMap;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import android.app.ListActivity;
import android.app.ProgressDialog;
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

public class ListUser extends ListActivity{
	
	private EditText search_user;
	private ListAdapter adapter;
	String year;
	ProgressDialog progressDialog;
	
	public void onCreate(Bundle onlist){
		super.onCreate(onlist);
		setContentView(R.layout.form_online_list);
		
		Intent in = getIntent();
		year = in.getStringExtra("year");
		
		showUser su = new showUser();
		su.execute("http://192.168.1.66:8080/chat/list_user.php?angkatan="+year);
		
		search_user = (EditText) findViewById(R.id.txt_search_on);
		
		search_user.addTextChangedListener(new TextWatcher() {

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
				((SimpleAdapter) ListUser.this.adapter).getFilter().filter(s);
				
			}
           
        });
    }
	
	class showUser extends AsyncTask<String, Integer, ArrayList<HashMap<String, String>> >{
		
		protected void onPreExecute() {
	        super.onPreExecute();
	        progressDialog= new ProgressDialog(ListUser.this);
	        progressDialog.setMessage("Loading ..");
	        progressDialog.setIndeterminate(false);
	        progressDialog.setCancelable(true);
	        progressDialog.show();
	    }

		ArrayList<HashMap<String, String>> mylist = new ArrayList<HashMap<String, String>>();

		@Override
		protected ArrayList<HashMap<String, String>> doInBackground(String... params) {
			// TODO Auto-generated method stub
			try {
				JSONObject json = JSONParser.getJSONfromURL(params[0]); 
				JSONArray list_user = json.getJSONArray("list_user");	

				for (int i = 0; i < list_user.length(); i++){
					JSONObject jsonobj = list_user.getJSONObject(i);
					
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
			progressDialog.dismiss();
			adapter = new SimpleAdapter(ListUser.this, mylist , 
					R.layout.data_online_list,
					new String[] { "id", "nama", "angkatan", "status_profile"},
					new int[] { R.id.txt_id_user, R.id.txt_nama, R.id.txt_angkatan, R.id.txt_status_user_on});
			setListAdapter(adapter);
	        
	        
	        final ListView lv = getListView();
	        lv.setOnItemClickListener(new OnItemClickListener() {
	        	public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
	        		String id_user = ((TextView) view.findViewById(R.id.txt_id_user)).getText().toString();
	        		
					
				}
			});
		
		}

	}
	
}
